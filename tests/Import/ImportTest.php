<?php
namespace App\Tests\API1;

use App\Entity\Account;
use App\Entity\APIAccessToken;
use App\Entity\Country;
use App\Entity\Event;
use App\Entity\Import;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Library;
use App\RepositoryQuery\EventRepositoryQuery;
use App\Tests\BaseWebTestWithDataBase;
use Sabre\VObject;

class ImportTest extends BaseWebTestWithDataBase
{

    
    protected $owner;
    protected $country;
    protected $timezone;
    protected $account;
    protected $token;
    
    private function setupCommon() {

        list($this->country, $this->timezone) = $this->createCountryDataForUK();
        list($this->owner, $this->account) = $this->createUserAndAccount('test1', $this->country, $this->timezone);

    }
    
    public function testBasic1() {

        $this->setupCommon();

        $import = new Import();
        $import->setNewIdAndSlug();
        $import->setAccount($this->account);
        $import->setTitle('Test');
        $import->setUrl('http://example.com');
        $import->setDefaultCountry($this->country);
        $import->setDefaultTimezone($this->timezone);
        $import->setPrivacy(0);
        $import->setEnabled(true);

        $this->entityManager->persist($import);
        $this->entityManager->flush();

        $vcalendar = VObject\Reader::read(file_get_contents(__DIR__. '/data/basic1.ics'), VObject\Reader::OPTION_FORGIVING);

        self::$container->get('app.import')->importVCalender($import, $vcalendar);


        $events = (new EventRepositoryQuery($this->entityManager))->getEvents();

        $this->assertSame(3, count($events));

        /** @var Event $event1 */
        $event1 = $events[0];
        $this->assertSame("9am Event", $event1->getTitle());
        $this->assertSame(False, $event1->isAllDay());
        // TODO THIS IS AN HOUR OUT!!
        $this->assertSame("2025-06-04T08:00:00+01:00", $event1->getStart('Europe/London')->format('c'));
        $this->assertSame("2025-06-04T09:00:00+01:00", $event1->getEnd('Europe/London')->format('c'));

        /** @var Event $event2 */
        $event2 = $events[1];
        $this->assertSame("One Day Event", $event2->getTitle());
        $this->assertSame(True, $event2->isAllDay());
        $this->assertSame("2025-06-05T00:00:00+01:00", $event2->getStart('Europe/London')->format('c'));
        $this->assertSame("2025-06-05T23:59:59+01:00", $event2->getEnd('Europe/London')->format('c'));

        /** @var Event $event3 */
        $event3 = $events[2];
        $this->assertSame("Two Day Event", $event3->getTitle());
        $this->assertSame(True, $event3->isAllDay());
        $this->assertSame("2025-06-06T00:00:00+01:00", $event3->getStart('Europe/London')->format('c'));
        $this->assertSame("2025-06-07T23:59:59+01:00", $event3->getEnd('Europe/London')->format('c'));

    }



}