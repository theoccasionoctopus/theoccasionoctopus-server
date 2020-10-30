<?php
namespace App\Tests\EventToEventOccurrence;

use App\Entity\Account;
use App\Entity\Country;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Entity\Event;
use App\Entity\EventOccurrence;
use App\Tests\BaseTestWithDataBase;

class SimpleRRULETest extends BaseTestWithDataBase
{

    protected $country;
    protected $timezone;
    protected $account;
    protected $event;
    
    private function setupCommon() {

        list($this->country, $this->timezone) = $this->createCountryDataForUK();
        $this->account = $this->createAccount('test1', $this->country, $this->timezone);

    }

    private function setupCommonEventWinterToSummer() {

        $this->event = new Event();
        $this->event->setAccount($this->account);
        $this->event->setTimezone($this->timezone);
        $this->event->setCountry($this->country);
        // Note this is very carefully picked to go across the BST change on 30th march - we want to test events reoccur across that correctly!
        $this->event->setStartWithObject(new \DateTime('2025-03-24 09:00:00', new \DateTimeZone('UTC')));
        $this->event->setEndWithObject(new \DateTime('2025-03-24 10:00:00', new \DateTimeZone('UTC')));
        $this->event->setRrule("FREQ=WEEKLY;WKST=MO;COUNT=2;BYDAY=MO");
        $this->event->setTitle('Title');
        $this->event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $this->event->setPrivacy(0);

        $this->entityManager->persist($this->event);

        $this->entityManager->flush();
        $this->entityManager->clear();

        # Reload from DB for a more realistic test
        $this->event = $this->entityManager
            ->getRepository(Event::class)
            ->findOneBy(array('id'=>'36573fb9-a021-4005-9fd2-3034cda50a72'));
    }


    public function testCreationWinterToSummer() {

        $this->setupCommon();
        $this->setupCommonEventWinterToSummer();

        self::$container->get('app.eventToEventOccurrenceService')->process($this->event);

        $this->entityManager->clear();

        $eventOccurrences = $this->entityManager
            ->getRepository(EventOccurrence::class)
            ->findBy(array(), array('startEpoch' => 'ASC'))
        ;

        $this->assertSame(2, count($eventOccurrences));

        $this->assertEquals('2025-03-24T09:00:00+00:00', $eventOccurrences[0]->getStart('UTC')->format('c'));
        $this->assertEquals('2025-03-24T10:00:00+00:00', $eventOccurrences[0]->getEnd('UTC')->format('c'));
        $this->assertEquals('2025-03-24T09:00:00+00:00', $eventOccurrences[0]->getStart()->format('c'));
        $this->assertEquals('2025-03-24T10:00:00+00:00', $eventOccurrences[0]->getEnd()->format('c'));

        $this->assertEquals('2025-03-31T08:00:00+00:00', $eventOccurrences[1]->getStart('UTC')->format('c'));
        $this->assertEquals('2025-03-31T09:00:00+00:00', $eventOccurrences[1]->getEnd('UTC')->format('c'));
        $this->assertEquals('2025-03-31T09:00:00+01:00', $eventOccurrences[1]->getStart()->format('c'));
        $this->assertEquals('2025-03-31T10:00:00+01:00', $eventOccurrences[1]->getEnd()->format('c'));


    }


    private function setupCommonEventSummerToWinter() {

        $this->event = new Event();
        $this->event->setAccount($this->account);
        $this->event->setTimezone($this->timezone);
        $this->event->setCountry($this->country);
        // Note this is very carefully picked to go across the BST change on 26th Oct - we want to test events reoccur across that correctly!
        $this->event->setStartWithObject(new \DateTime('2025-10-20 09:00:00', new \DateTimeZone('Europe/London')));
        $this->event->setEndWithObject(new \DateTime('2025-10-20 10:00:00', new \DateTimeZone('Europe/London')));
        $this->event->setRrule("FREQ=WEEKLY;WKST=MO;COUNT=2;BYDAY=MO");
        $this->event->setTitle('Title');
        $this->event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $this->event->setPrivacy(0);

        $this->entityManager->persist($this->event);

        $this->entityManager->flush();
        $this->entityManager->clear();

        # Reload from DB for a more realistic test
        $this->event = $this->entityManager
            ->getRepository(Event::class)
            ->findOneBy(array('id'=>'36573fb9-a021-4005-9fd2-3034cda50a72'));

    }


    public function testCreationSummerToWinter() {

        $this->setupCommon();
        $this->setupCommonEventSummerToWinter();

        self::$container->get('app.eventToEventOccurrenceService')->process($this->event);

        $this->entityManager->clear();

        $eventOccurrences = $this->entityManager
            ->getRepository(EventOccurrence::class)
            ->findBy(array(), array('startEpoch' => 'ASC'))
        ;

        $this->assertSame(2, count($eventOccurrences));

        $this->assertEquals('2025-10-20T08:00:00+00:00', $eventOccurrences[0]->getStart('UTC')->format('c'));
        $this->assertEquals('2025-10-20T09:00:00+00:00', $eventOccurrences[0]->getEnd('UTC')->format('c'));
        $this->assertEquals('2025-10-20T09:00:00+01:00', $eventOccurrences[0]->getStart()->format('c'));
        $this->assertEquals('2025-10-20T10:00:00+01:00', $eventOccurrences[0]->getEnd()->format('c'));

        $this->assertEquals('2025-10-27T09:00:00+00:00', $eventOccurrences[1]->getStart('UTC')->format('c'));
        $this->assertEquals('2025-10-27T10:00:00+00:00', $eventOccurrences[1]->getEnd('UTC')->format('c'));
        $this->assertEquals('2025-10-27T09:00:00+00:00', $eventOccurrences[1]->getStart()->format('c'));
        $this->assertEquals('2025-10-27T10:00:00+00:00', $eventOccurrences[1]->getEnd()->format('c'));


    }

}
