<?php

namespace App\Service\Account;

use App\Entity\AccountFollowsAccount;
use App\Entity\AccountLocal;
use App\Entity\EventHasImport;
use App\Entity\EventHasSourceEvent;
use App\Entity\EventHasTag;
use App\Entity\Import;
use App\Entity\Source;
use App\Entity\SourceHasTag;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\History;
use App\Entity\Account;
use App\Entity\Event;
use App\Library;
use App\Message\NewAcceptRemoteAccountFollowingMessage;
use App\Message\NewFollowRemoteAccountMessage;
use App\Message\NewRejectRemoteAccountFollowingMessage;
use App\Message\NewUnfollowRemoteAccountMessage;
use App\Service\HistoryWorker\HistoryWorker;
use App\Service\HistoryWorker\HistoryWorkerService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Sabre\VObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AccountService
{

    /** @var  EntityManagerInterface */
    protected $entityManager;


    /** @var LoggerInterface  */
    protected $logger;

    /** @var MessageBusInterface  */
    protected $messageBus;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, MessageBusInterface $bus)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->messageBus = $bus;
    }


    public function follow(AccountLocal $accountLocal, Account $wantsToFollowAccount)
    {
        if ($accountLocal->getAccount()->getId() == $wantsToFollowAccount->getId()) {
            return;
        }

        /** @var AccountFollowsAccount $account_follows_account */
        $account_follows_account = $this->entityManager->getRepository(AccountFollowsAccount::class)->findOneBy(array('account' => $accountLocal->getAccount(), 'followsAccount' => $wantsToFollowAccount));
        if (!$account_follows_account) {
            $account_follows_account = new AccountFollowsAccount();
            $account_follows_account->setAccount($accountLocal->getAccount());
            $account_follows_account->setFollowsAccount($wantsToFollowAccount);
        }
        if ($account_follows_account->isFollows() || $account_follows_account->isFollowRequested()) {
            // It's already in progress; we do nothing
            return;
        }

        $account_follows_account->setActivitypubFollowActivityData(null);

        if ($wantsToFollowAccount->getAccountLocal()) {
            if ($wantsToFollowAccount->getAccountLocal()->isManuallyApprovesFollowers()) {
                $account_follows_account->setFollowRequested(true);
            } else {
                $account_follows_account->setFollows(true);
            }
            $this->entityManager->persist($account_follows_account);
            $this->entityManager->flush();
            $this->logger->info('New follow request made from local account to local account; granted immediately', ['account_id'=>$accountLocal->getAccount()->getId(), 'wants_to_follow_account_id'=>$wantsToFollowAccount->getId()]);
        } else {
            // If remote we have to request
            $account_follows_account->setFollowRequested(true);
            $this->entityManager->persist($account_follows_account);
            $this->entityManager->flush();
            $this->logger->info('New follow request made from local account to remote account; requested', ['account_id'=>$accountLocal->getAccount()->getId(), 'wants_to_follow_account_id'=>$wantsToFollowAccount->getId()]);
            $this->messageBus->dispatch(new NewFollowRemoteAccountMessage($accountLocal->getAccount()->getId(), $wantsToFollowAccount->getId()));
        }
    }


    public function unfollow(AccountLocal $accountLocal, Account $wantsToUnfollowAccount)
    {
        /** @var AccountFollowsAccount $account_follows_account */
        $account_follows_account = $this->entityManager->getRepository(AccountFollowsAccount::class)->findOneBy(array('account' => $accountLocal->getAccount(), 'followsAccount' => $wantsToUnfollowAccount));
        if (!$account_follows_account) {
            return;
        }
        if (!$account_follows_account->isFollows() && !$account_follows_account->isFollowRequested()) {
            return;
        }

        $account_follows_account->setFollows(false);
        $account_follows_account->setFollowRequested(false);
        $this->entityManager->persist($account_follows_account);
        $this->entityManager->flush();
        $this->logger->info('Account unfollow', ['account_id'=>$accountLocal->getAccount()->getId(), 'unfollow_account_id'=>$wantsToUnfollowAccount->getId()]);

        if ($wantsToUnfollowAccount->getAccountRemote()) {
            $this->messageBus->dispatch(new NewUnfollowRemoteAccountMessage($accountLocal->getAccount()->getId(), $wantsToUnfollowAccount->getId()));
        }
    }


    public function acceptFollower(AccountLocal $accountAccepting, Account $wantsToBeFollowerAccount)
    {
        /** @var AccountFollowsAccount $account_follows_account */
        $account_follows_account = $this->entityManager->getRepository(AccountFollowsAccount::class)->findOneBy(array('account' => $wantsToBeFollowerAccount, 'followsAccount' => $accountAccepting->getAccount()));
        if (!$account_follows_account) {
            return;
        }
        if ($account_follows_account->isFollows() || !$account_follows_account->isFollowRequested()) {
            return;
        }

        $activtypubFollowActivityData = $account_follows_account->getActivitypubFollowActivityData();
        $account_follows_account->setActivitypubFollowActivityData(null);
        $account_follows_account->setFollows(true);
        $account_follows_account->setFollowRequested(false);
        $this->entityManager->persist($account_follows_account);
        $this->entityManager->flush();
        $this->logger->info('Account accepts follower', ['account_id'=>$accountAccepting->getAccount()->getId(), 'wants_to_be_follower_account_id'=>$wantsToBeFollowerAccount->getId()]);

        if ($wantsToBeFollowerAccount->getAccountRemote()) {
            $this->messageBus->dispatch(new NewAcceptRemoteAccountFollowingMessage($accountAccepting->getAccount()->getId(), $wantsToBeFollowerAccount->getId(), $activtypubFollowActivityData));
        }
    }

    public function rejectFollower(AccountLocal $accountRejecting, Account $wantsToBeFollowerAccount)
    {
        $account_follows_account = $this->entityManager->getRepository(AccountFollowsAccount::class)->findOneBy(array('account' => $wantsToBeFollowerAccount, 'followsAccount' => $accountRejecting->getAccount()));
        if (!$account_follows_account) {
            return;
        }
        if (!$account_follows_account->isFollows() && !$account_follows_account->isFollowRequested()) {
            return;
        }

        $activtypubFollowActivityData = $account_follows_account->getActivitypubFollowActivityData();
        $account_follows_account->setActivitypubFollowActivityData(null);
        $account_follows_account->setFollows(false);
        $account_follows_account->setFollowRequested(false);
        $this->entityManager->persist($account_follows_account);
        $this->entityManager->flush();
        $this->logger->info('Account rejects follower', ['account_id'=>$accountRejecting->getAccount()->getId(), 'wants_to_be_follower_account_id'=>$wantsToBeFollowerAccount->getId()]);

        if ($wantsToBeFollowerAccount->getAccountRemote()) {
            $this->messageBus->dispatch(new NewRejectRemoteAccountFollowingMessage($accountRejecting->getAccount()->getId(), $wantsToBeFollowerAccount->getId(), $activtypubFollowActivityData));
        }
    }
}
