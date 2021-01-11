<?php

namespace App\Controller;

use App\APIV1\ICalBuilderForAccount;
use App\Entity\AccountRemote;
use App\Entity\InboxSubmission;
use App\Entity\UserManageAccount;
use App\FilterParams\EventListFilterParams;
use App\Library;
use App\Message\NewInboxSubmissionMessage;
use App\Service\AccountLocalInbox\AccountLocalInboxService;
use App\Service\AccountRemote\AccountRemoteService;
use App\Service\RemoteServer\RemoteServerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\Response;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Psr\Log\LoggerInterface;
use HttpSignatures\Context;

class APIActivityStreamsController extends BaseController
{

    /** @var  Account */
    protected $account;

    protected function buildAccount($account_id, Request $request)
    {
        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Account::class);
        $this->account = $repository->findOneById($account_id);
        if (!$this->account) {
            throw new  NotFoundHttpException('Not found');
        }
        if (!$this->account->getAccountLocal()) {
            // API should only be used on local accounts
            throw new  NotFoundHttpException('Not found');
        }
        if ($this->account->getAccountLocal()->isLocked()) {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function index($account_id, Request $request)
    {
        $this->buildAccount($account_id, $request);
        return $this->getResponseAccountActivityStreamsProfileJSON($this->account, $request);
    }

    public function inbox(
        $account_id,
        Request $request,
        LoggerInterface $logger,
        AccountLocalInboxService $accountLocalInboxService,
        RemoteServerService $remoteServerService,
        AccountRemoteService $accountRemoteService
    ) {
        $entityManager = $this->getDoctrine()->getManager();
        if (!$this->getParameter('app.instance_federation')) {
            return new Response(
                json_encode(['error'=>'federation_off']),
                Response::HTTP_SERVICE_UNAVAILABLE,
                ['content-type' => 'application/json']
            );
        }
        $this->buildAccount($account_id, $request);

        // Get data and make entity
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new Response(
                json_encode(['error'=>'no-data']),
                500,
                ['content-type' => 'application/json']
            );
        }

        // In case it crashes later in this block, we still want to know what the message was
        // Our use of fingers_crossed monolog logger means this is not loged normally, but is logged if there is a crash
        $logger->debug(
            "Account Inbox got message",
            [
                'account_id'=>$this->account->getId(),
                'data'=>$data,
                'user_agent'=>$request->headers->get('User-Agent'),
                'ip'=>$request->getClientIp(),
            ]
        );

        $inboxSubmission = new InboxSubmission();
        $inboxSubmission->setId(Library::GUID());
        $inboxSubmission->setAccount($this->account);
        $inboxSubmission->setData($data);
        $inboxSubmission->setIp($request->getClientIp());
        $inboxSubmission->setUseragent($request->headers->get('User-Agent'));

        // Do we handle request? If not just say we accept it and drop it
        if (!$accountLocalInboxService->canProcessInboxSubmission($inboxSubmission)) {
            $logger->info(
                "Account Inbox got message, but we do not handle it so we just dropped it (http signature not verified)",
                [
                    'account_id'=>$this->account->getId(),
                    'data'=>$data,
                    'user_agent'=>$request->headers->get('User-Agent'),
                    'ip'=>$request->getClientIp(),
                ]
            );
            return new Response(
                json_encode([]),
                Response::HTTP_OK,
                ['content-type' => 'application/json']
            );
        }

        // Verify Signature
        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $psrRequest = $psrHttpFactory->createRequest($request);

        $actorId = $data['actor'];
        $remoteServer = $remoteServerService->getOrCreateByUrl($actorId);
        $accountRemote = $accountRemoteService->getOrCreateByActorId($remoteServer, $actorId);
        $context = new Context([
            'keys' => [
                $actorId.'#main-key' => $accountRemote->getActorData()['publicKey']['publicKeyPem']
            ],
        ]);
        if (!$context->verifier()->isSigned($psrRequest)) {
            $logger->info(
                "Account Inbox got message, but we could not verify it was valid!",
                [
                    'account_id'=>$this->account->getId(),
                    'data'=>$data,
                    'user_agent'=>$request->headers->get('User-Agent'),
                    'ip'=>$request->getClientIp(),
                ]
            );
            return new Response(
                json_encode(['error'=>'signature-verification-failed']),
                401,
                ['content-type' => 'application/json']
            );
        }
        // TODO $context->verifier()->isSignedWithDigest($message); but only if digest present - some servers may not send

        // Process!
        $entityManager->persist($inboxSubmission);
        $entityManager->flush();

        $logger->info(
            "Account Inbox got message",
            [
                'account_id'=>$this->account->getId(),
                'data'=>$data,
                'user_agent'=>$request->headers->get('User-Agent'),
                'ip'=>$request->getClientIp(),
            ]
        );

        $this->dispatchMessage(new NewInboxSubmissionMessage($inboxSubmission->getId()));

        return new Response(
            json_encode([]),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    public function outbox($account_id, Request $request)
    {
        if (!$this->getParameter('app.instance_federation')) {
            return new Response(
                json_encode(['error'=>'federation_off']),
                Response::HTTP_SERVICE_UNAVAILABLE,
                ['content-type' => 'application/json']
            );
        }
        $this->buildAccount($account_id, $request);

        $out = [
            "@context"=> ["https://www.w3.org/ns/activitystreams"],
            "type"=> "OrderedCollectionPage",
            # TOOD "id": "",
            "orderedItems"=> [],
        ];

        $params = new EventListFilterParams($this->getDoctrine(), $this->account);
        $params->build($request->query);
        $params->getRepositoryQuery()->setPublicOnly();
        $events = $params->getRepositoryQuery()->getEvents();

        /** @var Event $event */
        foreach ($events as $event) {
            $out['orderedItems'][] = [
                'type'=> 'Create',
                'object'=>[
                    'type'=>'Event',
                    'id'=>$this->getParameter('app.instance_url').$this->generateUrl('account_public_event_show_event', ['account_username'=>$this->account->getUsername(),'event_id'=>$event->getId()]),
                    'name'=>$event->getTitle(),
                    'summary'=>str_replace("\n", '<p>', htmlspecialchars($event->getDescription())),
                    'startTime'=>$event->getStart('UTC')->format('Y-m-d\TH:i:s'),
                    'endTime'=>$event->getEnd('UTC')->format('Y-m-d\TH:i:s'),
                    'url'=>$this->getParameter('app.instance_url').$this->generateUrl('account_public_event_show_event', ['account_username'=>$this->account->getUsername(),'event_id'=>$event->getId()]),
                ]
            ];
        }

        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }
}
