<?php

namespace App\Service\AccountLocalInbox;

use App\Entity\AccountRemote;
use App\Entity\InboxSubmission;
use App\Service\AccountRemote\AccountRemoteService;
use App\Service\RemoteAccountContent\RemoteAccountContentService;
use App\Service\RemoteServer\RemoteServerService;
use App\Service\RequestHTTP\RequestHTTPService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountLocalInboxService
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
     * @var RemoteServerService
     */
    protected $remoteServerService;

    /**
     * @var AccountRemoteService
     */
    protected $accountRemoteService;

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

    protected function getHandlers(): array
    {
        return [
            new ProcessInboxSubmissionFollow(
                $this->entityManager,
                $this->logger,
                $this->requestHTTPService,
                $this->accountRemoteService,
                $this->remoteServerService,
                $this->remoteAccountContentService
            ),
            new ProcessInboxSubmissionAcceptFollow(
                $this->entityManager,
                $this->logger,
                $this->requestHTTPService,
                $this->accountRemoteService,
                $this->remoteServerService,
                $this->remoteAccountContentService
            ),
            new ProcessInboxSubmissionRejectFollow(
                $this->entityManager,
                $this->logger,
                $this->requestHTTPService,
                $this->accountRemoteService,
                $this->remoteServerService,
                $this->remoteAccountContentService
            ),
            new ProcessInboxSubmissionUndoFollow(
                $this->entityManager,
                $this->logger,
                $this->requestHTTPService,
                $this->accountRemoteService,
                $this->remoteServerService,
                $this->remoteAccountContentService
            ),
            new ProcessInboxSubmissionCreateUpdateEvent(
                $this->entityManager,
                $this->logger,
                $this->requestHTTPService,
                $this->accountRemoteService,
                $this->remoteServerService,
                $this->remoteAccountContentService
            )
        ];
    }

    public function processInboxSubmission(InboxSubmission $inboxSubmission)
    {
        foreach ($this->getHandlers() as $handler) {
            if ($handler->canHandle($inboxSubmission)) {
                $handler->handle($inboxSubmission);
                return;
            }
        }
    }

    public function canProcessInboxSubmission(InboxSubmission $inboxSubmission): bool
    {
        foreach ($this->getHandlers() as $handler) {
            if ($handler->canHandle($inboxSubmission)) {
                return true;
            }
        }
        return false;
    }
}
