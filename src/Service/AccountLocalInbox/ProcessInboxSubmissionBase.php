<?php

namespace App\Service\AccountLocalInbox;

use App\Entity\InboxSubmission;
use App\Service\AccountRemote\AccountRemoteService;
use App\Service\RemoteAccountContent\RemoteAccountContentService;
use App\Service\RemoteServer\RemoteServerService;
use App\Service\RequestHTTP\RequestHTTPService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class ProcessInboxSubmissionBase
{

    /** @var  EntityManagerInterface */
    protected $entityManager;


    /** @var LoggerInterface  */
    protected $logger;

    /**
     * @var RequestHTTPService
     */
    protected $requestHTTPService;

    /**
     * @var AccountRemoteService
     */
    protected $accountRemoteService;

    /**
     * @var RemoteServerService
     */
    protected $remoteServerService;

    /**
     * @var RemoteAccountContentService
     */
    protected $remoteAccountContentService;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        RequestHTTPService $requestHTTPService,
        AccountRemoteService $accountRemoteService,
        RemoteServerService $remoteServerService,
        RemoteAccountContentService $remoteAccountContentService
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->requestHTTPService = $requestHTTPService;
        $this->accountRemoteService = $accountRemoteService;
        $this->remoteServerService = $remoteServerService;
        $this->remoteAccountContentService = $remoteAccountContentService;
    }

    protected function markInboxSubmissionProcessed(InboxSubmission $inboxSubmission)
    {
        $inboxSubmission->setProcessed(time());
        $this->entityManager->persist($inboxSubmission);
    }

    abstract public function canHandle(InboxSubmission $inboxSubmission);

    abstract public function handle(InboxSubmission $inboxSubmission);
}
