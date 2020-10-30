<?php
namespace App\Tests\EventToEventOccurrence;

use App\Entity\Account;
use App\Entity\Country;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Entity\Event;
use App\Entity\EventOccurrence;
use App\Tests\BaseTestWithDataBase;

class NoRRULETest extends BaseTestWithDataBase
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
        $this->event->setStartWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $this->event->setEndWithObject(new \DateTime('2025-01-01 11:00:00', new \DateTimeZone('UTC')));
        $this->event->setTitle('Title');
        $this->event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $this->event->setPrivacy(0);

        $this->entityManager->persist($this->event);
        $this->entityManager->flush();


    }
    
    public function testCreation() {

        $this->setupCommon();

        self::$container->get('app.eventToEventOccurrenceService')->process($this->event);

        $this->entityManager->clear();

        $eventOccurrences = $this->entityManager
            ->getRepository(EventOccurrence::class)
            ->findAll()
        ;

        $this->assertSame(1, count($eventOccurrences));
        $this->assertEquals('2025-01-01T10:00:00+00:00', $eventOccurrences[0]->getStart('UTC')->format('c'));
        $this->assertEquals('2025-01-01T11:00:00+00:00', $eventOccurrences[0]->getEnd('UTC')->format('c'));
        $this->assertEquals('2025-01-01T10:00:00+00:00', $eventOccurrences[0]->getStart()->format('c'));
        $this->assertEquals('2025-01-01T11:00:00+00:00', $eventOccurrences[0]->getEnd()->format('c'));

    }

    public function testCreationThenRunAgain() {

        $this->setupCommon();

        self::$container->get('app.eventToEventOccurrenceService')->process($this->event);
        // The second time it runs it should see the existing records and cope fine
        self::$container->get('app.eventToEventOccurrenceService')->process($this->event);

        $this->entityManager->clear();

        $eventOccurrences = $this->entityManager
            ->getRepository(EventOccurrence::class)
            ->findAll()
        ;

        $this->assertSame(1, count($eventOccurrences));
        $this->assertEquals('2025-01-01T10:00:00+00:00', $eventOccurrences[0]->getStart('UTC')->format('c'));
        $this->assertEquals('2025-01-01T11:00:00+00:00', $eventOccurrences[0]->getEnd('UTC')->format('c'));
        $this->assertEquals('2025-01-01T10:00:00+00:00', $eventOccurrences[0]->getStart()->format('c'));
        $this->assertEquals('2025-01-01T11:00:00+00:00', $eventOccurrences[0]->getEnd()->format('c'));

    }

    public function testCreationThenChangeEndDateAndRunAgain() {

        $this->setupCommon();

        // Run once to generate
        self::$container->get('app.eventToEventOccurrenceService')->process($this->event);


        $this->entityManager->clear();
        $eventOccurrences = $this->entityManager
            ->getRepository(EventOccurrence::class)
            ->findAll()
        ;
        $this->assertSame(1, count($eventOccurrences));
        $this->assertEquals('2025-01-01T10:00:00+00:00', $eventOccurrences[0]->getStart('UTC')->format('c'));
        $this->assertEquals('2025-01-01T11:00:00+00:00', $eventOccurrences[0]->getEnd('UTC')->format('c'));
        $this->assertEquals('2025-01-01T10:00:00+00:00', $eventOccurrences[0]->getStart()->format('c'));
        $this->assertEquals('2025-01-01T11:00:00+00:00', $eventOccurrences[0]->getEnd()->format('c'));

        // Change end time
        $this->entityManager->clear();
        $event = $this->entityManager->getRepository(Event::class)->findAll()[0];
        $event->setEndWithObject(new \DateTime('2025-01-01 12:00:00', new \DateTimeZone('UTC')));
        $this->entityManager->persist($event);
        $this->entityManager->flush();


        // The second time it runs it should change the end date
        self::$container->get('app.eventToEventOccurrenceService')->process($event);

        $this->entityManager->clear();
        $eventOccurrences = $this->entityManager
            ->getRepository(EventOccurrence::class)
            ->findAll()
        ;
        $this->assertSame(1, count($eventOccurrences));
        $this->assertEquals('2025-01-01T10:00:00+00:00', $eventOccurrences[0]->getStart('UTC')->format('c'));
        $this->assertEquals('2025-01-01T12:00:00+00:00', $eventOccurrences[0]->getEnd('UTC')->format('c'));
        $this->assertEquals('2025-01-01T10:00:00+00:00', $eventOccurrences[0]->getStart()->format('c'));
        $this->assertEquals('2025-01-01T12:00:00+00:00', $eventOccurrences[0]->getEnd()->format('c'));

    }

    public function testCreationThenChangeStartAndEndDateAndRunAgain() {

        $this->setupCommon();

        // Run once to generate
        self::$container->get('app.eventToEventOccurrenceService')->process($this->event);


        $this->entityManager->clear();
        $eventOccurrences = $this->entityManager
            ->getRepository(EventOccurrence::class)
            ->findAll()
        ;
        $this->assertSame(1, count($eventOccurrences));
        $this->assertEquals('2025-01-01T10:00:00+00:00', $eventOccurrences[0]->getStart('UTC')->format('c'));
        $this->assertEquals('2025-01-01T11:00:00+00:00', $eventOccurrences[0]->getEnd('UTC')->format('c'));
        $this->assertEquals('2025-01-01T10:00:00+00:00', $eventOccurrences[0]->getStart()->format('c'));
        $this->assertEquals('2025-01-01T11:00:00+00:00', $eventOccurrences[0]->getEnd()->format('c'));

        // Change start and end time
        $this->entityManager->clear();
        $event = $this->entityManager->getRepository(Event::class)->findAll()[0];
        $event->setStartWithObject(new \DateTime('2025-01-01 17:00:00', new \DateTimeZone('UTC')));
        $event->setEndWithObject(new \DateTime('2025-01-01 19:00:00', new \DateTimeZone('UTC')));
        $this->entityManager->persist($event);
        $this->entityManager->flush();


        // The second time it runs it should change the end date
        self::$container->get('app.eventToEventOccurrenceService')->process($event);

        $this->entityManager->clear();
        $eventOccurrences = $this->entityManager
            ->getRepository(EventOccurrence::class)
            ->findAll()
        ;
        $this->assertSame(1, count($eventOccurrences));
        $this->assertEquals('2025-01-01T17:00:00+00:00', $eventOccurrences[0]->getStart('UTC')->format('c'));
        $this->assertEquals('2025-01-01T19:00:00+00:00', $eventOccurrences[0]->getEnd('UTC')->format('c'));
        $this->assertEquals('2025-01-01T17:00:00+00:00', $eventOccurrences[0]->getStart()->format('c'));
        $this->assertEquals('2025-01-01T19:00:00+00:00', $eventOccurrences[0]->getEnd()->format('c'));

    }

}
