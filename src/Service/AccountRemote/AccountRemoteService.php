<?php

namespace App\Service\AccountRemote;

use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\AccountRemote;
use App\Entity\RemoteServer;
use App\Library;
use App\Service\RequestHTTP\RequestHTTPService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        LoggerInterface $logger,
        UrlGeneratorInterface $router,
        RequestHTTPService $requestHTTPService
    ) {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->logger = $logger;
        $this->router = $router;
        $this->requestHTTPService = $requestHTTPService;
    }

    public function addByUsername(RemoteServer $remoteServer, string $username): Account
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
                return $account;
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
                return $account->getAccount();
            }
        }

        // Now save.
        $account = new Account();
        $account->setId($isOccasionOctopus ? $dataWebFinger['occasion-octopus-id'] : Library::GUID());
        $account->setTitle($isOccasionOctopus ? $dataWebFinger['occasion-octopus-title'] : $dataActor['name']);

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

        return $account;
    }


    public function addByActorId(RemoteServer $remoteServer, string $actorId): Account
    {
        // Check account not already here
        $accountRemote = $this->entityManager->getRepository(AccountRemote::class)->findOneBy(['actorDataId'=>$actorId,'remoteServer'=>$remoteServer]);
        if ($accountRemote) {
            return $accountRemote->getAccount();
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

        return $account;
    }

    public function sendFollowRequest(AccountLocal $account, AccountRemote $wantsToFollowAccount)
    {
        $this->postToInbox(
            $account,
            $wantsToFollowAccount,
            [
                "id"=> $this->params->get('app.instance_url') . '/activitypubactivity/followrequest/'.$account->getAccount()->getId().'/'.urlencode($wantsToFollowAccount->getActorDataId()),
                "type"=> "Follow",
                "actor"=> $this->params->get('app.instance_url') . $this->router->generate('account_public', ['account_username'=>$account->getUsername()]),
                "object"=> $wantsToFollowAccount->getActorDataId(),
                "@context"=> "https://www.w3.org/ns/activitystreams",
            ]
        );
    }

    public function sendFollowAccept(AccountRemote $account, AccountLocal $wantsToFollowAccount, array $objectData)
    {
        $this->postToInbox(
            $wantsToFollowAccount,
            $account,
            [
                "id"=> $this->params->get('app.instance_url') . '/activitypubactivity/followrequestapproved/'.$wantsToFollowAccount->getAccount()->getId().'/'.urlencode($account->getActorDataId()),
                "type"=> "Accept",
                "actor"=> $this->params->get('app.instance_url') . $this->router->generate('account_public', ['account_username'=>$wantsToFollowAccount->getUsername()]),
                "object"=> $objectData,
                "@context"=> "https://www.w3.org/ns/activitystreams",
            ]
        );
    }

    public function postToInbox(AccountLocal $fromAccountLocal, AccountRemote $toAccount, $data)
    {
        if (!$toAccount->getActorData() || !array_key_exists('inbox', $toAccount->getActorData())) {
            throw new \Exception('Can not find inbox of remote account');
        }
        $url = $toAccount->getActorData()['inbox'];

        $response = $this->requestHTTPService->request(
            "POST",
            $url,
            array('http_errors' => false, 'body'=>json_encode($data))
        );
        if ($response->getStatusCode() != 200) {
            throw new \Exception("When Posting to inbox, Got Status " . $response->getStatusCode());
        }
        $this->logger->info('Posted to ActivityPub inbox', ['url'=>$url, 'data'=>json_encode($data)]);
    }
}