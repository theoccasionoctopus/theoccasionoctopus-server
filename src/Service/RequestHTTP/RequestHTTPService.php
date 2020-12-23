<?php

namespace App\Service\RequestHTTP;

use GuzzleHttp\Client;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class RequestHTTPService
{

    /** @var GuzzleHttp\Client */
    protected $client;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /** @var LoggerInterface  */
    protected $logger;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger)
    {
        $this->params = $params;
        $this->logger = $logger;
    }


    public function request(string $method, $uri = '', array $options = []): ResponseInterface
    {
        if (!$this->client) {
            $this->client = new Client(
                array(
                    'headers' => array(  'User-Agent'=> $this->params->get('app.instance_url').' - '.$this->params->get('app.instance_name'))
                )
            );
        }
        return $this->client->request($method, $uri, $options);
    }
}
