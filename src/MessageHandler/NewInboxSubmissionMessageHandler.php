<?php

namespace App\MessageHandler;

use App\Entity\InboxSubmission;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\NewInboxSubmissionMessage;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\AccountLocalInbox\AccountLocalInboxService;

class NewInboxSubmissionMessageHandler implements MessageHandlerInterface
{

    /** @var AccountLocalInboxService */
    protected $accountLocalInboxService;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager, AccountLocalInboxService $accountLocalInboxService)
    {
        $this->entityManager = $entityManager;
        $this->accountLocalInboxService = $accountLocalInboxService;
    }

    public function __invoke(NewInboxSubmissionMessage $message)
    {
        $inboxSubmission = $this->entityManager->getRepository(InboxSubmission::class)->findOneBy(['id'=>$message->getInboxSubmissionId()]);
        $this->accountLocalInboxService->processInboxSubmission($inboxSubmission);
    }
}
