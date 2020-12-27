<?php

namespace App\Service\AccountLocalInbox;

use App\Entity\Account;
use App\Entity\AccountFollowsAccount;
use App\Entity\AccountLocal;
use App\Entity\AccountRemote;
use App\Entity\InboxSubmission;
use App\Entity\RemoteServer;
use App\Library;
use App\Service\RequestHTTP\RequestHTTPService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProcessInboxSubmissionFollow extends ProcessInboxSubmissionBase
{
    public function canHandle(InboxSubmission $inboxSubmission)
    {
        return $inboxSubmission->getData()['type'] == 'Follow';
    }

    public function handle(InboxSubmission $inboxSubmission)
    {
        $actorId = $inboxSubmission->getData()['actor'];

        // Sort out remote server
        $remoteServer = $this->remoteServerService->getOrCreateByUrl($actorId);

        // Find or create Accounts in our database
        $accountWantsToFollowLocal = $inboxSubmission->getAccount();
        $accountRemote = $this->accountRemoteService->getOrCreateByActorId($remoteServer, $actorId);

        // Save to database
        // At moment we always accept follow request straight away TODO have a mode where they have to be approved
        $account_follows_account = $this->entityManager->getRepository(AccountFollowsAccount::class)->findOneBy(
            array('account' => $accountRemote->getAccount(), 'followsAccount' => $accountWantsToFollowLocal)
        );
        if (!$account_follows_account) {
            $account_follows_account = new AccountFollowsAccount();
            $account_follows_account->setAccount($accountRemote->getAccount());
            $account_follows_account->setFollowsAccount($accountWantsToFollowLocal);
        }
        $account_follows_account->setFollowRequested(false);
        $account_follows_account->setFollows(true);
        $this->entityManager->persist($account_follows_account);
        $this->markInboxSubmissionProcessed($inboxSubmission);
        $this->entityManager->flush();

        $this->logger->info(
            'Approving remote account wants to follow local account request',
            ['local_account_id'=>$accountWantsToFollowLocal->getId(),'remote_account_id'=>$accountRemote->getAccount()->getId()]
        );


        // Send Accept back
        $this->accountRemoteService->sendFollowAccept($accountRemote, $accountWantsToFollowLocal->getAccountLocal(), $inboxSubmission->getData());
    }
}
