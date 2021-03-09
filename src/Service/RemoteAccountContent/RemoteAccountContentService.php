<?php

namespace App\Service\RemoteAccountContent;

use App\ActivityPub\APEvent;
use App\ActivityPub\APOutbox;
use App\Entity\Account;
use App\Entity\AccountRemote;
use App\Entity\Country;
use App\Entity\EmailUserUpcomingEventsForAccount;
use App\Entity\Event;
use App\Entity\RemoteServer;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Library;
use App\Service\EventToEventOccurrence\EventToEventOccurrenceService;
use App\Service\RequestHTTP\RequestHTTPService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class RemoteAccountContentService
{

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /** @var LoggerInterface  */
    protected $logger;

    protected $eventToEventOccurrenceService;

    /** @var  RequestHTTPService */
    protected $requestHTTPService;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, EventToEventOccurrenceService $eventToEventOccurrenceService, RequestHTTPService $requestHTTPService)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->eventToEventOccurrenceService = $eventToEventOccurrenceService;
        $this->requestHTTPService = $requestHTTPService;
    }

    public function downloadAccountRemote(AccountRemote $accountRemote)
    {
        if ($accountRemote->getRemoteServer()->getOccasionOctopusSoftware()) {
            $this->downloadAccountRemoteFromOccasionOctopus($accountRemote);
        } else {
            $this->downloadAccountRemoteFromActivityPubServer($accountRemote);
        }
    }

    protected function downloadAccountRemoteFromOccasionOctopus(AccountRemote $accountRemote)
    {

        /** @var Account $account */
        $account = $accountRemote->getAccount();

        $this->logger->info('Downloading remote user content', ['account_id'=>$account->getId(),'server_software'=>'occasion_octopus']);

        // Check if remote server is still running our software
        // TODO now we work with servers of both types, can this check be cleverer? Or not done here?
        $response = $this->requestHTTPService->request("GET", $accountRemote->getRemoteServer()->getURL()."/.well-known/occasion-octopus-instance.json", array());
        if ($response->getStatusCode() != 200) {
            throw new Exception("Is remote software not our server? Got Status " . $response->getStatusCode());
        }

        // Get account info
        // TODO get a profile.json method on our own API, update remote title

        // Get Events
        $response = $this->requestHTTPService->request("GET", $accountRemote->getRemoteServer()->getURL()."/api/v1/account/".$account->getId()."/events.json", array());
        if ($response->getStatusCode() != 200) {
            throw new Exception("When Getting Events, Got Status " . $response->getStatusCode());
        }

        $APIEventListData = json_decode($response->getBody(), true);

        foreach ($APIEventListData['events'] as $eventData) {
            $this->updateEventWithOccasionOctopusData($accountRemote, $eventData);
        }
    }

    public function downloadEventFromOccasionOctopus(AccountRemote $accountRemote, string $eventId)
    {
        /** @var Account $account */
        $account = $accountRemote->getAccount();

        $this->logger->info('Downloading remote user content', ['account_id'=>$account->getId(),'server_software'=>'occasion_octopus']);

        // Check if remote server is still running our software
        // TODO Do we need to?

        // Get Event
        $response = $this->requestHTTPService->request("GET", $accountRemote->getRemoteServer()->getURL()."/api/v1/account/".$account->getId()."/event/".$eventId.".json", array());
        if ($response->getStatusCode() != 200) {
            throw new Exception("When Getting Event, Got Status " . $response->getStatusCode());
        }

        $APIEventData = json_decode($response->getBody(), true);

        $this->updateEventWithOccasionOctopusData($accountRemote, $APIEventData['event']);
    }

    protected function updateEventWithOccasionOctopusData(AccountRemote $accountRemote, array $eventData)
    {
        // We don't bother checking event is in future here; we assume those checks are done upstream
        $event = $this->entityManager->getRepository(Event::class)->findOneBy(array('id'=>$eventData['id'], 'account'=>$accountRemote->getAccount()));
        if (!$event) {
            $event = new Event();
            $event->setId($eventData['id']);
            $event->setAccount($accountRemote->getAccount());
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


        if ($eventData['all_day']) {
            $event->setStartWithInts(
                $eventData['start_timezone']['year'],
                $eventData['start_timezone']['month'],
                $eventData['start_timezone']['day'],
                null,
                null,
                null
            );

            $event->setEndWithInts(
                $eventData['end_timezone']['year'],
                $eventData['end_timezone']['month'],
                $eventData['end_timezone']['day'],
                null,
                null,
                null
            );
        } else {
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
        }

        // TODO extra fields
        // TODO Cancelled
        // TODO Deleted

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        // Event to event occurrence!
        $this->eventToEventOccurrenceService->process($event);

        // TODO this wont deal with things that were once public, new private! Special flag in API?
    }




    protected function downloadAccountRemoteFromActivityPubServer(AccountRemote $accountRemote)
    {
        $this->logger->info('Downloading remote user content', ['account_id' => $accountRemote->getAccount()->getId(), 'server_software' => 'activitypub']);

        $actorData = $accountRemote->getActorData();
        $outboxNextURL = is_array($actorData) && array_key_exists('outbox', $actorData) ? $actorData['outbox'] : null;

        while ($outboxNextURL) {
            $outbox = APOutbox::getAndCreate($outboxNextURL);

            foreach ($outbox->getItems() as $item) {
                if ($item->isObjectTypeEvent() && $item->isTypeCreate()) {
                    $this->updateEventWithActivityPubServerData($accountRemote, $item->getObject());
                }
            }

            $outboxNextURL = $outbox->getNextURL();
        }
    }

    public function updateEventWithActivityPubServerData(AccountRemote $accountRemote, APEvent $apEvent)
    {
        if ($apEvent->getEnd()->getTimestamp() > time()) {
            $event = $this->entityManager->getRepository(Event::class)->findOneBy(array('activitypubId' => $apEvent->getId(), 'account' => $accountRemote->getAccount()));
            if (!$event) {
                $event = new Event();
                $event->setId(Library::GUID());
                $event->setAccount($accountRemote->getAccount());
                $event->setPrivacy(0);
                $event->setActivitypubId($apEvent->getId());
                // TODO better setCountry and setTimezone needed
                $event->setCountry($this->entityManager->getRepository(Country::class)->findOneBy(['iso3166_two_char' => 'GB']));
                $event->setTimezone($this->entityManager->getRepository(TimeZone::class)->findOneByCode('Europe/London'));
            }

            $event->setTitle($apEvent->getName());
            $event->setDescriptionFromHTML($apEvent->getSummary());
            $event->setUrl($apEvent->getURL());
            $event->setUrlTickets($apEvent->getURL());

            $event->setStartWithObject($apEvent->getStart());
            $event->setEndWithObject($apEvent->getEnd());

            // TODO extra fields
            // TODO Cancelled
            // TODO Deleted

            $this->entityManager->persist($event);
            $this->entityManager->flush();

            // Event to event occurrence!
            $this->eventToEventOccurrenceService->process($event);
            // TODO: also if any events are sourced from this event, need to update them straight away
        }
    }
}
