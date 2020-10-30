<?php

namespace App\Service\RemoteServer;

use App\Entity\RemoteServer;
use App\Library;
use GuzzleHttp\Client;
use Doctrine\ORM\EntityManagerInterface;

class RemoteServerService
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

    public function add(string $url) {

        list($ssl, $host) = Library::parseURLToSSLAndHost($url);

        $remoteServer = $this->entityManager->getRepository(RemoteServer::class)->findOneByHost($host);

        if ($remoteServer) {
            return $remoteServer;
        }

        $remoteServer = new RemoteServer();
        $remoteServer->setHost($host);
        $remoteServer->setSSL($ssl);

        $guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'Prototype Software') )));
        $response = $guzzle->request("GET", $remoteServer->getURL()."/.well-known/occasion-octopus-instance.json", array());
        if ($response->getStatusCode() != 200) {
            throw new Exception("Got Status " . $response->getStatusCode());
        }

        $data = json_decode($response->getBody(), true);

        // TODO check api_version

        $remoteServer->setTitle($data['instance_name']);

        $this->entityManager->persist($remoteServer);
        $this->entityManager->flush();

        return $remoteServer;

    }

    public function addByHostName(string $host) {
        // We assume HTTPS - we should try and fall back to HTTP if it's not there
        return $this->add('https://'. $host);
    }

}

