<?php

namespace App\MessageHandler;

use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\AccountRemote;
use App\Entity\Import;
use App\Message\NewAcceptRemoteAccountFollowingMessage;
use App\Message\NewFollowRemoteAccountMessage;
use App\Message\NewRejectRemoteAccountFollowingMessage;
use App\Service\Import\ImportService;
use App\Service\AccountRemote\AccountRemoteService;
use App\Service\RemoteAccountContent\RemoteAccountContentService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\NewImportMessage;
use Doctrine\ORM\EntityManagerInterface;

class NewRejectRemoteAccountFollowingMessageHandler implements MessageHandlerInterface
{

    /** @var AccountRemoteService */
    protected $remoteAccountService;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /**
     * @var RemoteAccountContentService
     */
    protected $remoteAccountContentService;

    public function __construct(EntityManagerInterface $entityManager, AccountRemoteService $remoteAccountService, RemoteAccountContentService $remoteAccountContentService)
    {
        $this->entityManager = $entityManager;
        $this->remoteAccountService = $remoteAccountService;
        $this->remoteAccountContentService = $remoteAccountContentService;
    }

    public function __invoke(NewRejectRemoteAccountFollowingMessage $message)
    {
        $accountRepo = $this->entityManager->getRepository(Account::class);
        $accountRejecting = $accountRepo->findOneBy(['id'=>$message->getAccountRejectingId()]);
        if (!$accountRejecting) {
            throw new \Exception('No Account Found');
        }
        $wantsToBeFollowerAccount = $accountRepo->findOneBy(['id'=>$message->getWantToBeFollowerAccountId()]);
        if (!$wantsToBeFollowerAccount) {
            throw  new \Exception('No Account Wants To Follow Found');
        }

        $this->remoteAccountService->sendFollowReject($wantsToBeFollowerAccount->getAccountRemote(), $accountRejecting->getAccountLocal(), $message->getActivtypubFollowActivityData());
    }
}
