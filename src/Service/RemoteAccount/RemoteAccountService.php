<?php

namespace App\Service\RemoteAccount;

use App\Entity\Account;
use App\Entity\AccountRemote;
use App\Entity\RemoteServer;
use App\Library;
use GuzzleHttp\Client;
use Doctrine\ORM\EntityManagerInterface;

class RemoteAccountService
{

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(RemoteServer $remoteServer, string $username)
    {
        $guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'Prototype Software') )));
        $resourceToGetFromWebFinger = 'acct:'. $username. '@'. $remoteServer->getHost();
        $responseWebFinger = $guzzle->request(
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
        $responseActorData = $guzzle->request(
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
}
