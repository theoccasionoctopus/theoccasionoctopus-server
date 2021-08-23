<?php
namespace App\Tests\API1;

use App\Entity\Account;
use App\Entity\AccountFollowsAccount;
use App\Entity\APIAccessToken;
use App\Entity\Country;
use App\Entity\Event;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Library;
use App\RepositoryQuery\EventRepositoryQuery;
use App\Tests\BaseTestWithDataBase;

class EventRepositoryQueryLongEventTest extends BaseTestWithDataBase
{

    protected $country;
    protected $timezone;
    protected $account;
    protected $event;

    private function setupCommon() {

        list($this->country, $this->timezone) = $this->createCountryDataForUK();
        $this->account = $this->createAccount('test1', $this->country, $this->timezone);

        $this->event = new Event();
        $this->event->setAccount($this->account);
        $this->event->setTimezone($this->timezone);
        $this->event->setCountry($this->country);
        $this->event->setStartWithObject(new \DateTime('2025-10-01 10:00:00', new \DateTimeZone('UTC')));
        $this->event->setEndWithObject(new \DateTime('2025-12-01 11:00:00', new \DateTimeZone('UTC')));
        $this->event->setTitle('Title Other Event');
        $this->event->setId('12573fb9-a021-4005-9fd2-3034cda50a12');
        $this->event->setSlug('12573fb9-a021-4005-9fd2-3034cda50a12');
        $this->event->setPrivacy(0);

        $this->entityManager->persist($this->event);

        $this->entityManager->flush();

        static::$kernel->getContainer()->get('app.eventToEventOccurrenceService')->process($this->event);
    }


    public function testAllEventsMode() {

        $this->setupCommon();

        # Test Events
        $this->entityManager->clear();
        $eventRepositoryQuery = new EventRepositoryQuery($this->doctrine);
        $eventRepositoryQuery->setAccountEvents($this->account);
        // The next line is the default option, so we don't need to call it. But it's what we are testing here:
        // $eventRepositoryQuery->setStartEndMode(EventRepositoryQuery::START_END_MODE_ALL_EVENTS);
        $eventRepositoryQuery->setFrom(new \DateTime('2025-11-01'));
        $eventRepositoryQuery->setTo(new \DateTime('2025-11-02'));
        $events = $eventRepositoryQuery->getEvents();

        $this->assertEquals(1, count($events));
        $this->assertEquals($this->event->getStart('UTC')->format('c'), $events[0]->getStart('UTC')->format('c'));

        # Test Event Occurrences
        $this->entityManager->clear();
        $eventRepositoryQuery = new EventRepositoryQuery($this->doctrine);
        $eventRepositoryQuery->setAccountEvents($this->account);
        // The next line is the default option, so we don't need to call it. But it's what we are testing here:
        // $eventRepositoryQuery->setStartEndMode(EventRepositoryQuery::START_END_MODE_ALL_EVENTS);
        $eventRepositoryQuery->setFrom(new \DateTime('2025-11-01'));
        $eventRepositoryQuery->setTo(new \DateTime('2025-11-02'));
        $eventOccurrences = $eventRepositoryQuery->getEventOccurrences();

        $this->assertEquals(1, count($eventOccurrences));
        $this->assertEquals($this->event->getStart('UTC')->format('c'), $eventOccurrences[0]->getStart('UTC')->format('c'));
    }


    public function testStartingOnlyMode1() {

        $this->setupCommon();

        # Test Events
        $this->entityManager->clear();
        $eventRepositoryQuery = new EventRepositoryQuery($this->doctrine);
        $eventRepositoryQuery->setAccountEvents($this->account);
        $eventRepositoryQuery->setStartEndMode(EventRepositoryQuery::START_END_MODE_STARTING_EVENTS_ONLY);
        $eventRepositoryQuery->setFrom(new \DateTime('2025-11-01'));
        $eventRepositoryQuery->setTo(new \DateTime('2025-11-02'));
        $events = $eventRepositoryQuery->getEvents();

        $this->assertEquals(0, count($events));

        # Test Event Occurrences
        $this->entityManager->clear();
        $eventRepositoryQuery = new EventRepositoryQuery($this->doctrine);
        $eventRepositoryQuery->setAccountEvents($this->account);
        $eventRepositoryQuery->setStartEndMode(EventRepositoryQuery::START_END_MODE_STARTING_EVENTS_ONLY);
        $eventRepositoryQuery->setFrom(new \DateTime('2025-11-01'));
        $eventRepositoryQuery->setTo(new \DateTime('2025-11-02'));
        $eventOccurrences = $eventRepositoryQuery->getEventOccurrences();

        $this->assertEquals(0, count($eventOccurrences));
    }




}