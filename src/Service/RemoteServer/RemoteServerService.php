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

    public function getOrCreateByUrl(string $url): RemoteServer
    {
        list($ssl, $host) = Library::parseURLToSSLAndHost($url);

        $remoteServer = $this->entityManager->getRepository(RemoteServer::class)->findOneByHost($host);

        if ($remoteServer) {
            return $remoteServer;
        }

        $remoteServer = new RemoteServer();
        $remoteServer->setHost($host);
        $remoteServer->setSSL($ssl);

        $guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'Prototype Software') )));
        $response = $guzzle->request(
            "GET",
            $remoteServer->getURL()."/.well-known/occasion-octopus-instance.json",
            array('http_errors' => false)
        );

        if ($response->getStatusCode() == 404) {
            // It's not a Occasion Octopus server!
            // But we got a 404, so there is some server there.
            return $this->addActivityPubRemoteServer($remoteServer);
        } elseif ($response->getStatusCode() == 200) {
            // It might is a Occasion Octopus server
            $data = json_decode($response->getBody(), true);
            if (is_array($data) && array_key_exists('instance_name', $data)) {
                // It is a Occasion Octopus server
                return $this->addOccasionOctopusRemoteServer($data, $remoteServer);
            } else {
                // we have seen other sites return a 200 to our special URL but then not be our software!
                return $this->addActivityPubRemoteServer($remoteServer);
            }
        } else {
            throw new \Exception("Got Status " . $response->getStatusCode());
        }
    }

    protected function addOccasionOctopusRemoteServer($data, RemoteServer $remoteServer): RemoteServer
    {
        // TODO check api_version
        $remoteServer->setTitle($data['instance_name']);
        $remoteServer->setOccasionOctopusSoftware(true);

        $this->entityManager->persist($remoteServer);
        $this->entityManager->flush();

        return $remoteServer;
    }

    protected function addActivityPubRemoteServer(RemoteServer $remoteServer): RemoteServer
    {
        $remoteServer->setTitle($remoteServer->getHost());
        $remoteServer->setOccasionOctopusSoftware(false);

        $this->entityManager->persist($remoteServer);
        $this->entityManager->flush();

        return $remoteServer;
    }

    public function addByHostName(string $host)
    {
        // We assume HTTPS - we should try and fall back to HTTP if it's not there
        try {
            return $this->getOrCreateByUrl('https://' . $host);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return $this->getOrCreateByUrl('http://' . $host);
        }
    }
}
