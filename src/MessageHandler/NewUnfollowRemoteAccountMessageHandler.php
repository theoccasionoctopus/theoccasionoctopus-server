<?php

namespace App\MessageHandler;

use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\AccountRemote;
use App\Entity\Import;
use App\Message\NewFollowRemoteAccountMessage;
use App\Message\NewUnfollowRemoteAccountMessage;
use App\Service\Import\ImportService;
use App\Service\AccountRemote\AccountRemoteService;
use App\Service\RemoteAccountContent\RemoteAccountContentService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\NewImportMessage;
use Doctrine\ORM\EntityManagerInterface;

class NewUnfollowRemoteAccountMessageHandler implements MessageHandlerInterface
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

    public function __invoke(NewUnfollowRemoteAccountMessage $message)
    {
        $accountRepo = $this->entityManager->getRepository(Account::class);
        $account = $accountRepo->findOneBy(['id'=>$message->getAccountId()]);
        $wantsToUnfollowAccount = $accountRepo->findOneBy(['id'=>$message->getUnfollowsAccountId()]);
        $this->remoteAccountService->sendUnfollow($account->getAccountLocal(), $wantsToUnfollowAccount->getAccountRemote());
    }
}
