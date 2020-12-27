<?php

namespace App\MessageHandler;

use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\AccountRemote;
use App\Entity\Import;
use App\Message\NewAcceptRemoteAccountFollowingMessage;
use App\Message\NewFollowRemoteAccountMessage;
use App\Service\Import\ImportService;
use App\Service\AccountRemote\AccountRemoteService;
use App\Service\RemoteAccountContent\RemoteAccountContentService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\NewImportMessage;
use Doctrine\ORM\EntityManagerInterface;

class NewAcceptRemoteAccountFollowingMessageHandler implements MessageHandlerInterface
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

    public function __invoke(NewAcceptRemoteAccountFollowingMessage $message)
    {
        $accountRepo = $this->entityManager->getRepository(Account::class);
        $accountAccepting = $accountRepo->findOneBy(['id'=>$message->getAccountAcceptingId()]);
        if (!$accountAccepting) {
            throw new \Exception('No Account Found');
        }
        $wantsToBeFollowerAccount = $accountRepo->findOneBy(['id'=>$message->getWantToBeFollowerAccountId()]);
        if (!$wantsToBeFollowerAccount) {
            throw  new \Exception('No Account Wants To Follow Found');
        }

        $this->remoteAccountService->sendFollowAccept($wantsToBeFollowerAccount->getAccountRemote(), $accountAccepting->getAccountLocal(), $message->getActivtypubFollowActivityData());
    }
}
