<?php

namespace App\Controller;

use App\Entity\AccountLocal;
use App\Entity\InboxSubmission;
use App\Entity\Note;
use App\Entity\Tag;
use App\Entity\User;
use App\FilterParams\EventListFilterParams;
use App\Library;
use App\Message\NewInboxSubmissionMessage;
use App\Service\AccountLocalInbox\AccountLocalInboxService;
use App\Service\AccountRemote\AccountRemoteService;
use App\Service\ActivityPubData\ActivityPubDataService;
use App\Service\RemoteServer\RemoteServerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Psr\Log\LoggerInterface;
use HttpSignatures\Context;

class AccountIdPublicController extends BaseController
{

    /** @var  Account */
    protected $account;

    protected $account_permission_read_only_followers = false;


    protected function setUpAccountByIdPublic($account_id, Request $request)
    {
        $this->setUp($request);
        $doctrine = $this->getDoctrine();
        // Load Account
        $this->account = $doctrine->getRepository(Account::class)->findOneById($account_id);
        if (!$this->account) {
            throw new  NotFoundHttpException('Not found');
        }
        if (!$this->account->getAccountLocal()) {
            // should only be used on local accounts
            throw new  NotFoundHttpException('Not found');
        }
        if ($this->account->getAccountLocal()->isLocked()) {
            throw new  NotFoundHttpException('Not found');
        }
        // If user is logged in, do they have special read permissions here?
        $user= $this->get('security.token_storage')->getToken()->getUser();
        if ($user && $user instanceof User) {
            if ($doctrine->getRepository(Account::class)->findAccountsManagedByUserThatFollowsThisAccount($user, $this->account)) {
                $this->account_permission_read_only_followers = true;
            }
        }
    }


    public function indexAccount($account_id, Request $request, ActivityPubDataService $activityPubDataService)
    {
        $this->setUpAccountByIdPublic($account_id, $request);

        if ($this->isRequestForActivityPubJSON($request)) {
            return new Response(
                json_encode($activityPubDataService->generateActorForAccount($this->account), JSON_PRETTY_PRINT),
                Response::HTTP_OK,
                ['content-type' => 'application/activity+json']
            );
        } else {
            return $this->redirectToRoute('account_public', ['account_username' => $this->account->getUsername() ]);
        }
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
        // TODO if read only mode .....
        $this->setUpAccountByIdPublic($account_id, $request);

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

    public function outbox($account_id, Request $request, ActivityPubDataService $activityPubDataService)
    {
        if (!$this->getParameter('app.instance_federation')) {
            return new Response(
                json_encode(['error'=>'federation_off']),
                Response::HTTP_SERVICE_UNAVAILABLE,
                ['content-type' => 'application/json']
            );
        }
        $this->setUpAccountByIdPublic($account_id, $request);

        $out = [
            "@context"=> ["https://www.w3.org/ns/activitystreams"],
            "type"=> "OrderedCollectionPage",
            # TOOD "id": "",
            "orderedItems"=> [],
        ];

        # Events
        $params = new EventListFilterParams($this->getDoctrine(), $this->account);
        $params->build($request->query);
        $params->getRepositoryQuery()->setPublicOnly();
        $events = $params->getRepositoryQuery()->getEvents();

        /** @var Event $event */
        foreach ($events as $event) {
            $out['orderedItems'][] = $activityPubDataService->generateCreateActivityForEvent($event);
        }

        # Notes
        $notes = $this->getDoctrine()->getRepository(Note::class)->getForOutboxOfAccount($this->account);
        foreach ($notes as $note) {
            $out['orderedItems'][] = $activityPubDataService->generateCreateActivityForNote($note);
        }

        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/activity+json']
        );
    }


    public function follow($account_id, $remote_account_id, Request $request, ActivityPubDataService $activityPubDataService)
    {
        throw new  NotFoundHttpException('Not found');
    }


    public function unfollow($account_id, $remote_account_id, Request $request, ActivityPubDataService $activityPubDataService)
    {
        throw new  NotFoundHttpException('Not found');
    }


    public function acceptFollow($account_id, $remote_account_id, Request $request, ActivityPubDataService $activityPubDataService)
    {
        throw new  NotFoundHttpException('Not found');
    }


    public function rejectFollow($account_id, $remote_account_id, Request $request, ActivityPubDataService $activityPubDataService)
    {
        throw new  NotFoundHttpException('Not found');
    }
}
