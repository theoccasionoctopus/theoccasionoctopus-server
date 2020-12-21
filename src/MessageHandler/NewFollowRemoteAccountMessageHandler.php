<?php

namespace App\MessageHandler;

use App\Entity\Account;
use App\Entity\AccountRemote;
use App\Entity\Import;
use App\Message\NewFollowRemoteAccountMessage;
use App\Service\Import\ImportService;
use App\Service\RemoteAccountContent\RemoteAccountContentService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\NewImportMessage;
use Doctrine\ORM\EntityManagerInterface;

class NewFollowRemoteAccountMessageHandler implements MessageHandlerInterface
{
    protected $remoteAccountContentService;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager, RemoteAccountContentService $remoteAccountContentService)
    {
        $this->entityManager = $entityManager;
        $this->remoteAccountContentService = $remoteAccountContentService;
    }

    public function __invoke(NewFollowRemoteAccountMessage $message)
    {
        $account = $this->entityManager->getRepository(Account::class)->findOneBy(['id'=>$message->getFollowsAccountId()]);
        $accountRemote = $this->entityManager->getRepository(AccountRemote::class)->findOneBy(['account'=>$account]);
        $this->remoteAccountContentService->downloadAccountRemote($accountRemote);
    }
}
