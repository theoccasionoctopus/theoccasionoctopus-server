<?php

namespace App\Service\AccountRemote;

use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\AccountRemote;
use App\Entity\Note;
use App\Entity\RemoteServer;
use App\Entity\RemoteServerSendData;
use App\Library;
use App\Message\SendRemoteServerSendDataMessage;
use App\Service\ActivityPubData\ActivityPubDataService;
use App\Service\RequestHTTP\RequestHTTPService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use GuzzleHttp\Psr7\Request as Psr7Request;
use HttpSignatures\Context;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class AccountRemoteService
{

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /** @var LoggerInterface  */
    protected $logger;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var RequestHTTPService
     */
    protected $requestHTTPService;

    /** @var  ActivityPubDataService */
    protected $activityPubDataService;

    /** @var MessageBusInterface  */
    protected $messageBus;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        LoggerInterface $logger,
        UrlGeneratorInterface $router,
        RequestHTTPService $requestHTTPService,
        ActivityPubDataService $activityPubDataService,
        MessageBusInterface $bus
    ) {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->logger = $logger;
        $this->router = $router;
        $this->requestHTTPService = $requestHTTPService;
        $this->activityPubDataService = $activityPubDataService;
        $this->messageBus = $bus;
    }

    public function getOrCreateByUsername(RemoteServer $remoteServer, string $username): AccountRemote
    {
        $resourceToGetFromWebFinger = 'acct:'. $username. '@'. $remoteServer->getHost();
        $responseWebFinger = $this->requestHTTPService->request(
            "GET",
            $remoteServer->getURL()."/.well-known/webfinger?resource=". urlencode($resourceToGetFromWebFinger),
            array()
        );
        if ($responseWebFinger->getStatusCode() != 200) {
            throw new Exception("Got Status " . $responseWebFinger->getStatusCode());
        }

        $dataWebFinger = json_decode($responseWebFinger->getBody(), true);
        $isOccasionOctopus = array_key_exists('occasion-octopus-id', $dataWebFinger);

        // Check account not already here - if Occasion Octopus
        if ($isOccasionOctopus) {
            $account = $this->entityManager->getRepository(Account::class)->findOneById($dataWebFinger['occasion-octopus-id']);
            if ($account) {
                // TODO check that account actually is a remote account on this server - if not we have an ID collision!
                return $account->getAccountRemote();
            }
        }

        // Get account data
        $actorDataURL = Library::getActivityStreamsActorURLFromWebFingerData($dataWebFinger);
        $responseActorData = $this->requestHTTPService->request(
            "GET",
            $actorDataURL,
            array(
                'headers' => [
                    'Accept'     => 'application/activity+json',
                ]
            )
        );
        if ($responseActorData->getStatusCode() != 200) {
            throw new Exception("Got Status " . $responseActorData->getStatusCode());
        }

        $dataActor = json_decode($responseActorData->getBody(), true);

        // Check account not already here - if not Occasion Octopus
        if (!$isOccasionOctopus) {
            $account = $this->entityManager->getRepository(AccountRemote::class)->findOneBy(['actorDataId'=>$dataActor['id'],'remoteServer'=>$remoteServer]);
            if ($account) {
                return $account;
            }
        }

        // Now save.
        $account = new Account();
        $account->setId($isOccasionOctopus ? $dataWebFinger['occasion-octopus-id'] : Library::GUID());
        if ($isOccasionOctopus) {
            $account->setTitle($dataWebFinger['occasion-octopus-title']);
        } else {
            $account->setTitle($dataActor['name'] ? $dataActor['name'] : $username);
        }

        $accountRemote = new AccountRemote();
        $accountRemote->setAccount($account);
        $accountRemote->setRemoteServer($remoteServer);
        $accountRemote->setUsername($username);
        $accountRemote->setWebfingerData($dataWebFinger);
        $accountRemote->setWebfingerDataLastFetched(time());
        $accountRemote->setActorData($dataActor);
        $accountRemote->setActorDataLastFetched(time());
        $accountRemote->setActorDataId($dataActor['id']);

        $this->entityManager->persist($account);
        $this->entityManager->persist($accountRemote);
        $this->entityManager->flush();

        return $accountRemote;
    }


    public function getOrCreateByActorId(RemoteServer $remoteServer, string $actorId): AccountRemote
    {
        // Check account not already here
        $accountRemote = $this->entityManager->getRepository(AccountRemote::class)->findOneBy(['actorDataId'=>$actorId,'remoteServer'=>$remoteServer]);
        if ($accountRemote) {
            return $accountRemote;
        }

        // Get account data
        $responseActorData = $this->requestHTTPService->request(
            "GET",
            $actorId,
            array(
                'headers' => [
                    'Accept'     => 'application/activity+json',
                ]
            )
        );
        if ($responseActorData->getStatusCode() != 200) {
            throw new Exception("Got Status " . $responseActorData->getStatusCode());
        }

        $dataActor = json_decode($responseActorData->getBody(), true);


        // Now save.
        $account = new Account();
        $account->setId($remoteServer->getOccasionOctopusSoftware() ? $dataActor['occasion-octopus-id'] : Library::GUID());
        $account->setTitle($dataActor['name']);

        $accountRemote = new AccountRemote();
        $accountRemote->setAccount($account);
        $accountRemote->setRemoteServer($remoteServer);
        $accountRemote->setUsername(null);
        $accountRemote->setWebfingerData(null);
        $accountRemote->setWebfingerDataLastFetched(null);
        $accountRemote->setActorData($dataActor);
        $accountRemote->setActorDataLastFetched(time());
        $accountRemote->setActorDataId($dataActor['id']);

        $this->entityManager->persist($account);
        $this->entityManager->persist($accountRemote);
        $this->entityManager->flush();

        return $accountRemote;
    }

    public function sendFollowRequest(AccountLocal $account, AccountRemote $wantsToFollowAccount)
    {
        $this->postToInbox(
            $account,
            $wantsToFollowAccount,
            $this->activityPubDataService->generateFollowRequest($account, $wantsToFollowAccount)
        );
    }

    public function sendFollowAccept(AccountRemote $account, AccountLocal $wantsToFollowAccount, array $objectData)
    {
        $this->postToInbox(
            $wantsToFollowAccount,
            $account,
            $this->activityPubDataService->generateFollowAccept($account, $wantsToFollowAccount, $objectData)
        );
    }

    public function sendFollowReject(AccountRemote $account, AccountLocal $wantsToFollowAccount, array $objectData)
    {
        $this->postToInbox(
            $wantsToFollowAccount,
            $account,
            $this->activityPubDataService->generateFollowReject($account, $wantsToFollowAccount, $objectData)
        );
    }

    public function sendUnfollow(AccountLocal $account, AccountRemote $wantsToUnfollowAccount)
    {
        $this->postToInbox(
            $account,
            $wantsToUnfollowAccount,
            $this->activityPubDataService->generateUndoFollow($account, $wantsToUnfollowAccount)
        );
    }


    public function sendPublicNote(Note $note)
    {
        $data = $this->activityPubDataService->generateCreateActivityForNote($note);
        /** @var Account $remoteFollowerAccount */
        foreach ($this->entityManager->getRepository(Account::class)->findRemoteFollowers($note->getAccount()) as $remoteFollowerAccount) {
            $data['to'] = [
                $remoteFollowerAccount->getAccountRemote()->getActorDataId(),
                "https://www.w3.org/ns/activitystreams#Public",
            ];
            $this->postToInbox($note->getAccount()->getAccountLocal(), $remoteFollowerAccount->getAccountRemote(), $data);
        }
    }

    public function postToInbox(AccountLocal $fromAccountLocal, AccountRemote $toAccount, $data)
    {
        $remoteServerSendData = new RemoteServerSendData();
        $remoteServerSendData->setFromAccount($fromAccountLocal->getAccount());
        $remoteServerSendData->setToAccount($toAccount->getAccount());
        $remoteServerSendData->setData($data);

        $this->entityManager->persist($remoteServerSendData);
        $this->entityManager->flush();

        $this->messageBus->dispatch(
            new SendRemoteServerSendDataMessage($remoteServerSendData->getId())
        );
    }

    protected function errorInSendRemoteServerSendData(RemoteServerSendData $remoteServerSendData)
    {
        // TODO put all these constants in parameters
        $remoteServerSendData->increaseFailedCount();
        $this->entityManager->persist($remoteServerSendData);
        $this->entityManager->flush();
        if ($remoteServerSendData->getFailedCount() < 10) {
            $this->messageBus->dispatch(
                new SendRemoteServerSendDataMessage($remoteServerSendData->getId()),
                [
                    new DelayStamp(5*60*1000*$remoteServerSendData->getFailedCount())
                ]
            );
        }
    }

    public function sendRemoteServerSendData(RemoteServerSendData $remoteServerSendData)
    {
        $toAccountActorData = $remoteServerSendData->getToAccount()->getAccountRemote()->getActorData();
        if (!$toAccountActorData || !array_key_exists('inbox', $toAccountActorData)) {
            $this->logger->error(
                'When Posting to inbox, Can not find inbox of remote account',
                [
                    'remote_server_send_data_id'=>$remoteServerSendData->getId()
                ]
            );
            return $this->errorInSendRemoteServerSendData($remoteServerSendData);
        }
        $url = $toAccountActorData['inbox'];

        // Request
        $psrRequest = new Psr7Request(
            "POST",
            $url,
            [
                'date'=>(new \DateTime('', new \DateTimeZone('UTC')))->format('D, d M Y H:i:s \G\M\T'),
            ],
            json_encode($remoteServerSendData->getData())
        );

        // Sign
        $context = new Context([
            'keys' => [
                $remoteServerSendData->getData()['actor'].'#main-key' => $remoteServerSendData->getFromAccount()->getAccountLocal()->getKeyPrivate()
            ],
            'algorithm' => 'rsa-sha256',
            'headers' => ['(request-target)', 'host', 'date'],
        ]);
        $psrRequest = $context->signer()->signWithDigest($psrRequest);

        // Send
        try {
            $response = $this->requestHTTPService->send(
                $psrRequest,
                array(
                    'http_errors' => false,
                )
            );
            # TODO I would like to catch Guzzle errors only here but that is more difficult than it should be, for some reason
        } catch (\Exception $e) {
            $this->logger->error(
                'When Posting to inbox, got exception',
                [
                    'error'=>$e->getMessage(),
                    'error_class'=>get_class($e),
                    'remote_server_send_data_id'=>$remoteServerSendData->getId()
                ]
            );
            return $this->errorInSendRemoteServerSendData($remoteServerSendData);
        }
        if ($response->getStatusCode() == 200 || $response->getStatusCode() == 202) {
            $this->logger->info('Posted to ActivityPub inbox', ['url'=>$url, 'remote_server_send_data_id'=>$remoteServerSendData->getId()]);
            $remoteServerSendData->setSucceededNow();
            $this->entityManager->persist($remoteServerSendData);
            $this->entityManager->flush();
        } else {
            $this->logger->error(
                'When Posting to inbox, Got Status other than 200 or 202',
                [
                    'response_status'=>$response->getStatusCode(),
                    'response_content'=>$response->getBody(),
                    'remote_server_send_data_id'=>$remoteServerSendData->getId()
                ]
            );
            return $this->errorInSendRemoteServerSendData($remoteServerSendData);
        }
    }
}
