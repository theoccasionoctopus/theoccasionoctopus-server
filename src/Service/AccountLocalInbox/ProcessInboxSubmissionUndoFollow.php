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

class ProcessInboxSubmissionUndoFollow extends ProcessInboxSubmissionBase
{
    public function canHandle(InboxSubmission $inboxSubmission)
    {
        return $inboxSubmission->getData()['type'] == 'Undo' && $inboxSubmission->getData()['object']['type'] == 'Follow';
    }

    public function handle(InboxSubmission $inboxSubmission)
    {
        $actorId = $inboxSubmission->getData()['actor'];

        // Sort out remote server
        $remoteServer = $this->remoteServerService->add($actorId);

        // Find Accounts in our database
        $accountWantsToFollowLocal = $inboxSubmission->getAccount();
        $accountRemote = $this->entityManager->getRepository(AccountRemote::class)->findOneBy(array('actorDataId'=>$actorId,'remoteServer'=>$remoteServer));
        if (!$accountRemote) {
            return;
        }

        // Save to database
        $account_follows_account = $this->entityManager->getRepository(AccountFollowsAccount::class)->findOneBy(
            array('account' => $accountRemote->getAccount(), 'followsAccount' => $accountWantsToFollowLocal)
        );
        if (!$account_follows_account) {
            return;
        }
        $account_follows_account->setFollowRequested(false);
        $account_follows_account->setFollows(false);
        $this->entityManager->persist($account_follows_account);
        $this->markInboxSubmissionProcessed($inboxSubmission);
        $this->entityManager->flush();

        $this->logger->info(
            'Remote account stops following local account',
            ['local_account_id'=>$accountWantsToFollowLocal->getId(),'remote_account_id'=>$accountRemote->getAccount()->getId()]
        );
    }
}
