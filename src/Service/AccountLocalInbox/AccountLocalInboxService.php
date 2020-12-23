<?php

namespace App\Service\AccountLocalInbox;

use App\Entity\InboxSubmission;
use App\Service\AccountRemote\AccountRemoteService;
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
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        RequestHTTPService $requestHTTPService,
        AccountRemoteService $accountRemoteService,
        RemoteServerService $remoteServerService
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->requestHTTPService = $requestHTTPService;
        $this->accountRemoteService = $accountRemoteService;
        $this->remoteServerService = $remoteServerService;
    }

    public function processInboxSubmission(InboxSubmission $inboxSubmission)
    {
        $handlers = [
            new ProcessInboxSubmissionFollow(
                $this->entityManager,
                $this->logger,
                $this->requestHTTPService,
                $this->accountRemoteService,
                $this->remoteServerService
            ),
            new ProcessInboxSubmissionAcceptFollow(
                $this->entityManager,
                $this->logger,
                $this->requestHTTPService,
                $this->accountRemoteService,
                $this->remoteServerService
            )
        ];
        foreach ($handlers as $handler) {
            if ($handler->canHandle($inboxSubmission)) {
                return $handler->handle($inboxSubmission);
                // TODO set processed column on $inboxSubmission
            }
        }
    }
}
