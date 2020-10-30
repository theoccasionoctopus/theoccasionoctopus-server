<?php

namespace App\Service\RemoteUserContent;


use App\Entity\Account;
use App\Entity\AccountRemote;
use App\Entity\Country;
use App\Entity\EmailUserUpcomingEventsForAccount;
use App\Entity\Event;
use App\Entity\RemoteServer;
use App\Entity\TimeZone;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;

class RemoteUserContentService
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

    public function downloadAccountRemote(AccountRemote $accountRemote) {

        /** @var Account $account */
        $account = $accountRemote->getAccount();

        // Check if remote server is still running our software
        $guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'Prototype Software') )));
        $response = $guzzle->request("GET", $accountRemote->getRemoteServer()->getURL()."/.well-known/occasion-octopus-instance.json", array());
        if ($response->getStatusCode() != 200) {
            throw new Exception("Is remote software not our server? Got Status " . $response->getStatusCode());
        }

        // Get account info
        // TODO get a profile.json method on our own API, update remote title

        // Get Events
        $guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'Prototype Software') )));
        $response = $guzzle->request("GET", $accountRemote->getRemoteServer()->getURL()."/api/v1/account/".$account->getId()."/events.json", array());
        if ($response->getStatusCode() != 200) {
            throw new Exception("When Getting Events, Got Status " . $response->getStatusCode());
        }

        $APIEventListData = json_decode($response->getBody(), true);

        foreach($APIEventListData['events'] as $eventData) {

            $event = $this->entityManager->getRepository(Event::class)->findOneBy(array('id'=>$eventData['id'], 'account'=>$account));
            if (!$event) {
                $event = new Event();
                $event->setId($eventData['id']);
                $event->setAccount($account);
                $event->setPrivacy(0);
            }

            $event->setTitle($eventData['title']);
            $event->setDescription($eventData['description']);
            $event->setUrl($eventData['url']);
            $event->setUrlTickets($eventData['url_tickets']);

            $country = $this->entityManager->getRepository(Country::class)->findOneBy(array('iso3166_two_char'=>$eventData['country']['code']));
            if (!$country) {
                throw new Exception("Country not known! " . $eventData['country']['code']);
            }
            $event->setCountry($country);

            $timezone = $this->entityManager->getRepository(TimeZone::class)->findOneBy(array('code'=>$eventData['timezone']['code']));
            if (!$timezone) {
                throw new Exception("Timezone not known! " . $eventData['timezone']['code']);
            }
            $event->setTimezone($timezone);

            $event->setStartWithInts(
                $eventData['start_timezone']['year'],
                $eventData['start_timezone']['month'],
                $eventData['start_timezone']['day'],
                $eventData['start_timezone']['hour'],
                $eventData['start_timezone']['minute'],
                $eventData['start_timezone']['second']
            );

            $event->setEndWithInts(
                $eventData['end_timezone']['year'],
                $eventData['end_timezone']['month'],
                $eventData['end_timezone']['day'],
                $eventData['end_timezone']['hour'],
                $eventData['end_timezone']['minute'],
                $eventData['end_timezone']['second']
            );

            // TODO extra fields
            // TODO Cancelled
            // TODO Deleted

            $this->entityManager->persist($event);
            $this->entityManager->flush();

            // TODO event to event occurrence!

            // TODO this wont deal with things that were once public, new private! Special flag in API?


        }

    }

}

