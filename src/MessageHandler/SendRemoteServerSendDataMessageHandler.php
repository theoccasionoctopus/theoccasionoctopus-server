<?php

namespace App\MessageHandler;

use App\Entity\RemoteServerSendData;
use App\Message\SendRemoteServerSendDataMessage;
use App\Service\AccountRemote\AccountRemoteService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

class SendRemoteServerSendDataMessageHandler implements MessageHandlerInterface
{


    /** @var AccountRemoteService */
    protected $accountRemoteService;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager, AccountRemoteService $accountRemoteService)
    {
        $this->entityManager = $entityManager;
        $this->accountRemoteService = $accountRemoteService;
    }

    public function __invoke(SendRemoteServerSendDataMessage $message)
    {
        $data = $this->entityManager->getRepository(RemoteServerSendData::class)->findOneBy(['id'=>$message->getRemoteServerSendDataId()]);
        if (!$data) {
            throw new \Exception('No Data Found');
        }
        $this->accountRemoteService->sendRemoteServerSendData($data);
    }
}
