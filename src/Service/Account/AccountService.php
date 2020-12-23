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
use App\Message\NewFollowRemoteAccountMessage;
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


    public function follow(Account $account, Account $wantsToFollowAccount)
    {
        if ($account->getId() == $wantsToFollowAccount->getId()) {
            return;
        }

        $wantsToFollowAccountLocal =  $this->entityManager->getRepository(AccountLocal::class)->findOneBy(array('account' => $wantsToFollowAccount));

        /** @var AccountFollowsAccount $account_follows_account */
        $account_follows_account = $this->entityManager->getRepository(AccountFollowsAccount::class)->findOneBy(array('account' => $account, 'followsAccount' => $wantsToFollowAccount));
        if (!$account_follows_account) {
            $account_follows_account = new AccountFollowsAccount();
            $account_follows_account->setAccount($account);
            $account_follows_account->setFollowsAccount($wantsToFollowAccount);
        }
        if ($account_follows_account->isFollows() || $account_follows_account->isFollowRequested()) {
            // It's already in progress; we do nothing
            return;
        }

        if ($wantsToFollowAccountLocal) {
            // If $wantsToFollowAccount is local then it's accepted straight away.
            $account_follows_account->setFollows(true);
            $this->entityManager->persist($account_follows_account);
            $this->entityManager->flush();
            $this->logger->info('New follow request made from local account to local account; granted immediately', ['account_id'=>$account->getId(), 'wants_to_follow_account_id'=>$wantsToFollowAccount->getId()]);
        } else {
            // If remote we have to request
            $account_follows_account->setFollowRequested(true);
            $this->entityManager->persist($account_follows_account);
            $this->entityManager->flush();
            $this->logger->info('New follow request made from local account to remote account; requested', ['account_id'=>$account->getId(), 'wants_to_follow_account_id'=>$wantsToFollowAccount->getId()]);
            $this->messageBus->dispatch(new NewFollowRemoteAccountMessage($account->getId(), $wantsToFollowAccount->getId()));
        }
    }
}
