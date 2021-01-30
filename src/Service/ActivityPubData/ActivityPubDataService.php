<?php

namespace App\Service\ActivityPubData;

use App\Entity\Event;
use App\Entity\Note;
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

    public function generateCreateActivityForEvent(Event $event): array
    {
        $out = [
            'type'=> 'Create',
            # TOOD "id": "",
            'actor'=> $this->params->get('app.instance_url') . $this->router->generate('account_public', ['account_username'=>$event->getAccount()->getUsername()]),
            "to"=> 	"https://www.w3.org/ns/activitystreams#Public",
            'object'=>$this->generateEventObject($event),
        ];
        return $out;
    }

    public function generateEventObject(Event $event): array
    {
        $out = [
            // These fields are ActivityPub standard
            'type'=>'Event',
            'id'=>$this->params->get('app.instance_url').$this->router->generate('account_public_event_show_event', ['account_username'=>$event->getAccount()->getUsername(),'event_id'=>$event->getId()]),
            'name'=>$event->getTitle(),
            'summary'=>str_replace("\n", '<p>', htmlspecialchars($event->getDescription())),
            'startTime'=>$event->getStart('UTC')->format('Y-m-d\TH:i:s'),
            'endTime'=>$event->getEnd('UTC')->format('Y-m-d\TH:i:s'),
            'url'=>$this->params->get('app.instance_url').$this->router->generate('account_public_event_show_event', ['account_username'=>$event->getAccount()->getUsername(),'event_id'=>$event->getId()]),
            // These fields are us
            'occasion_octopus' =>
            [
                'id'=>$event->getId(),
                'description'=>$event->getDescription(),
                'url'=>$event->getUrl(),
                'url_tickets'=>$event->getUrlTickets(),
                'timezone'=>['code'=>$event->getTimezone()->getCode()],
                'country'=>['code'=>$event->getCountry()->getIso3166TwoChar()],
                // TODO Add all the various start/end options we send in normal API
            ]
        ];
        return $out;
    }


    public function generateCreateActivityForNote(Note $note): array
    {
        $published = new \DateTime('', new \DateTimeZone('UTC'));
        $published->setTimestamp($note->getCreated());
        return [
            "@context"=> "https://www.w3.org/ns/activitystreams",
            "type"=> "Create",
            "id"=> $this->params->get('app.instance_url') . '/activitypubactivity/notecreate/'.$note->getId(),
            "to"=> "https://www.w3.org/ns/activitystreams#Public",
            "actor"=> $this->params->get('app.instance_url') . $this->router->generate('account_public', ['account_username'=>$note->getAccount()->getUsername()]),
            "object"=>[
                "id"=> $this->params->get('app.instance_url') . '/activitypubobject/note/'.$note->getId(),
                "type"=> "Note",
                "attributedTo"=> $this->params->get('app.instance_url') . $this->router->generate('account_public', ['account_username'=>$note->getAccount()->getUsername()]),
                "content"=> $note->getContent(),
                "@context"=> "https://www.w3.org/ns/activitystreams",
                "published"=>$published->format('Y-m-d\TH:i:s\Z')
            ],
        ];
    }
}
