<?php

namespace App\Service\ActivityPubData;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityPubDataService
{

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(
        ParameterBagInterface $params,
        UrlGeneratorInterface $router
    ) {
        $this->params = $params;
        $this->router = $router;
    }
    public function generateEventObject(Event $event): array
    {
        $out = [
            'type'=>'Event',
            'id'=>$this->params->get('app.instance_url').$this->router->generate('account_public_event_show_event', ['account_username'=>$event->getAccount()->getUsername(),'event_id'=>$event->getId()]),
            'name'=>$event->getTitle(),
            'summary'=>str_replace("\n", '<p>', htmlspecialchars($event->getDescription())),
            'startTime'=>$event->getStart('UTC')->format('Y-m-d\TH:i:s'),
            'endTime'=>$event->getEnd('UTC')->format('Y-m-d\TH:i:s'),
            'id'=>$this->params->get('app.instance_url').$this->router->generate('account_public_event_show_event', ['account_username'=>$event->getAccount()->getUsername(),'event_id'=>$event->getId()]),
        ];
        return $out;
    }
}
