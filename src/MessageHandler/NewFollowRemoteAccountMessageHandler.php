<?php

namespace App\MessageHandler;

use App\Entity\Account;
use App\Entity\AccountRemote;
use App\Entity\Import;
use App\Message\NewFollowRemoteAccountMessage;
use App\Service\Import\ImportService;
use App\Service\RemoteUserContent\RemoteUserContentService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\NewImportMessage;
use Doctrine\ORM\EntityManagerInterface;

class NewFollowRemoteAccountMessageHandler implements MessageHandlerInterface
{
    protected $remoteUserContentService;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager, RemoteUserContentService $remoteUserContentService)
    {
        $this->entityManager = $entityManager;
        $this->remoteUserContentService = $remoteUserContentService;
    }

    public function __invoke(NewFollowRemoteAccountMessage $message)
    {
        $account = $this->entityManager->getRepository(Account::class)->findOneBy(['id'=>$message->getFollowsAccountId()]);
        $accountRemote = $this->entityManager->getRepository(AccountRemote::class)->findOneBy(['account'=>$account]);
        $this->remoteUserContentService->downloadAccountRemote($accountRemote);
    }
}
