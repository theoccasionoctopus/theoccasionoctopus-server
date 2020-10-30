<?php

namespace App\Service\RemoteAccount;


use App\Entity\Account;
use App\Entity\AccountRemote;
use App\Entity\RemoteServer;
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

    public function add(RemoteServer $remoteServer, string $username) {


        $guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'Prototype Software') )));
        $response = $guzzle->request(
            "GET", $remoteServer->getURL()."/.well-known/webfinger?resource=". urlencode($username),
            array()
        );
        if ($response->getStatusCode() != 200) {
            throw new Exception("Got Status " . $response->getStatusCode());
        }

        $data = json_decode($response->getBody(), true);

        if (!array_key_exists('occasion-octopus-id', $data)) {
            throw new Exception("This is not an Occasion Octopus Server");
        }

        $account = $this->entityManager->getRepository(Account::class)->findOneById($data['occasion-octopus-id']);
        if ($account) {

            // TODO check that account actually is a remote account on this server - if not we have an ID collision!

            return $account;
        }

        $account = new Account();
        $account->setId($data['occasion-octopus-id']);
        $account->setTitle($data['occasion-octopus-title']);

        $accountRemote = new AccountRemote();
        $accountRemote->setAccount($account);
        $accountRemote->setRemoteServer($remoteServer);
        // TODO we can get away with hard coding this now - as soon as we start adding other types of servers we'll need to fix this!
        $accountRemote->setHumanURL($remoteServer->getURL().'/a/'.$username);

        $this->entityManager->persist($account);
        $this->entityManager->persist($accountRemote);
        $this->entityManager->flush();

        return $account;

    }

}

