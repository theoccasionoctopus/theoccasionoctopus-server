<?php

namespace App\Service\RequestHTTP;

use GuzzleHttp\Client;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Psr7\Request as Psr7Request;

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
        $this->client = new Client(
            array(
                'headers' => array(  'User-Agent'=> $this->params->get('app.instance_url').' - '.$this->params->get('app.instance_name'))
            )
        );
    }


    public function request(string $method, $uri = '', array $options = []): ResponseInterface
    {
        return $this->client->request($method, $uri, $options);
    }

    public function send(Psr7Request $request, array $options = [])
    {
        return $this->client->send($request, $options);
    }
}
