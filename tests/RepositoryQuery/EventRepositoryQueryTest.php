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

class EventRepositoryQueryTest extends BaseTestWithDataBase
{

    protected $country;
    protected $timezone;
    protected $account;
    protected $otherAccount;
    protected $otherAccountEvent;

    private function setupCommon() {

        list($this->country, $this->timezone) = $this->createCountryDataForUK();
        $this->account = $this->createAccount('test1', $this->country, $this->timezone);

        # Create another account with a random event
        # Should not show up in results when setAccountEvents and setAccountDiscoverEvents work!
        $otherAccount = $this->createAccount('testOther', $this->country, $this->timezone);

        $otherAccountEvent = new Event();
        $otherAccountEvent->setAccount($otherAccount);
        $otherAccountEvent->setTimezone($this->timezone);
        $otherAccountEvent->setCountry($this->country);
        $otherAccountEvent->setStartWithObject(new \DateTime('2025-10-01 10:00:00', new \DateTimeZone('UTC')));
        $otherAccountEvent->setEndWithObject(new \DateTime('2025-10-01 11:00:00', new \DateTimeZone('UTC')));
        $otherAccountEvent->setTitle('Title Other Event');
        $otherAccountEvent->setId('12573fb9-a021-4005-9fd2-3034cda50a12');
        $otherAccountEvent->setPrivacy(0);

        $this->entityManager->persist($otherAccountEvent);


        $this->entityManager->flush();

        static::$kernel->getContainer()->get('app.eventToEventOccurrenceService')->process($otherAccountEvent);
    }


    /**
     * When we call setAccountEvents we should only get data from that account
     */
    public function testSetAccountEvents() {

        $this->setupCommon();

        # Save event
        $event = new Event();
        $event->setAccount($this->account);
        $event->setTimezone($this->timezone);
        $event->setCountry($this->country);
        $event->setStartWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $event->setEndWithObject(new \DateTime('2025-01-01 11:00:00', new \DateTimeZone('UTC')));
        $event->setTitle('Title');
        $event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(0);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        static::$kernel->getContainer()->get('app.eventToEventOccurrenceService')->process($event);

        # Test Events
        $this->entityManager->clear();
        $eventRepositoryQuery = new EventRepositoryQuery($this->doctrine);
        $eventRepositoryQuery->setAccountEvents($this->account);
        $events = $eventRepositoryQuery->getEvents();

        $this->assertEquals(1, count($events));
        $this->assertEquals($event->getStart('UTC')->format('c'), $events[0]->getStart('UTC')->format('c'));

        # Test Event Occurrences
        $this->entityManager->clear();
        $eventRepositoryQuery = new EventRepositoryQuery($this->doctrine);
        $eventRepositoryQuery->setAccountEvents($this->account);
        $eventOccurrences = $eventRepositoryQuery->getEventOccurrences();

        $this->assertEquals(1, count($eventOccurrences));
        $this->assertEquals($event->getStart('UTC')->format('c'), $eventOccurrences[0]->getStart('UTC')->format('c'));
    }

    /**
     * When we call setAccountDiscoverEvents we should only get data from that account
     */
    public function testSetAccountDiscoverEvents() {

        $this->setupCommon();

        # Save
        $followedAccount = $this->createAccount('TestFollowed', $this->country, $this->timezone);

        $accountFollowsAccount = new AccountFollowsAccount();
        $accountFollowsAccount->setAccount($this->account);
        $accountFollowsAccount->setFollowsAccount($followedAccount);
        $accountFollowsAccount->setFollows(true);
        $this->entityManager->persist($accountFollowsAccount);

        $event = new Event();
        $event->setAccount($followedAccount);
        $event->setTimezone($this->timezone);
        $event->setCountry($this->country);
        $event->setStartWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $event->setEndWithObject(new \DateTime('2025-01-01 11:00:00', new \DateTimeZone('UTC')));
        $event->setTitle('Title');
        $event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(0);
        $this->entityManager->persist($event);

        $this->entityManager->flush();

        # Test Events
        $this->entityManager->clear();
        $eventRepositoryQuery = new EventRepositoryQuery($this->doctrine);
        $eventRepositoryQuery->setAccountDiscoverEvents($this->account);
        $events = $eventRepositoryQuery->getEvents();

        $this->assertEquals(1, count($events));
        $this->assertEquals($event->getStart('UTC')->format('c'), $events[0]->getStart('UTC')->format('c'));

        # Test Event Occurrences
        # TODO

    }




}