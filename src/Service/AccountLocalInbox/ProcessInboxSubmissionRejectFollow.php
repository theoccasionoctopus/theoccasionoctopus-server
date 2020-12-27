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

class ProcessInboxSubmissionRejectFollow extends ProcessInboxSubmissionBase
{
    public function canHandle(InboxSubmission $inboxSubmission)
    {
        return $inboxSubmission->getData()['type'] == 'Reject' &&  $inboxSubmission->getData()['object']['type'] == 'Follow';
    }

    public function handle(InboxSubmission $inboxSubmission)
    {
        $actorId = $inboxSubmission->getData()['actor'];

        // Sort out remote server
        $remoteServer = $this->remoteServerService->getOrCreateByUrl($actorId);

        // Find accounts
        $accountFollowing = $inboxSubmission->getAccount();
        $accountBeingFollowedRemote = $this->entityManager->getRepository(AccountRemote::class)->findOneBy(array('actorDataId'=>$actorId,'remoteServer'=>$remoteServer));

        // Mark follow rejected!
        $account_follows_account = $this->entityManager->getRepository(AccountFollowsAccount::class)->findOneBy(
            array('account' => $accountFollowing, 'followsAccount' => $accountBeingFollowedRemote->getAccount())
        );
        if (!$account_follows_account) {
            throw new \Exception('Can not find follow!');
        }

        $account_follows_account->setFollowRequested(false);
        $account_follows_account->setFollows(false);
        $this->entityManager->persist($account_follows_account);
        $this->markInboxSubmissionProcessed($inboxSubmission);
        $this->entityManager->flush();

        $this->logger->info(
            'Got rejection that local account can follow remote account',
            ['local_account_id'=>$accountFollowing->getId(),'remote_account_id'=>$accountBeingFollowedRemote->getAccount()->getId()]
        );
    }
}
