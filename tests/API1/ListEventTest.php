<?php
namespace App\Tests\API1;

use App\Entity\Account;
use App\Entity\APIAccessToken;
use App\Entity\Country;
use App\Entity\Event;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Library;
use App\Tests\BaseWebTestWithDataBase;

class ListEventTest extends BaseWebTestWithDataBase
{


    protected $owner;
    protected $country;
    protected $timezone;
    protected $account;

    private function setupCommon()
    {

        list($this->country, $this->timezone) = $this->createCountryDataForUK();
        list($this->owner, $this->account) = $this->createUserAndAccount('test1', $this->country, $this->timezone);

    }

    public function testBasic()
    {

        $this->setupCommon();

        $event = new Event();
        $event->setAccount($this->account);
        $event->setTimezone($this->timezone);
        $event->setCountry($this->country);
        $event->setStartWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $event->setEndWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $event->setTitle('Title');
        $event->setUrl('https://www.theoccasionoctopus.net/');
        $event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(0);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/v1/account/'.$this->account->getId().'/events.json');
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(1, count($responseData['events']));
        $this->assertSame('Title', $responseData['events'][0]['title']);
        $this->assertSame('public', $responseData['events'][0]['privacy']);

    }

    public function testURLHit()
    {

        $this->setupCommon();

        $event = new Event();
        $event->setAccount($this->account);
        $event->setTimezone($this->timezone);
        $event->setCountry($this->country);
        $event->setStartWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $event->setEndWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $event->setTitle('Title');
        $event->setUrl('https://www.theoccasionoctopus.net/');
        $event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(0);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/v1/account/'.$this->account->getId().'/events.json?url=' . urlencode('https://www.theoccasionoctopus.net/'));
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(1, count($responseData['events']));
        $this->assertSame('Title', $responseData['events'][0]['title']);
        $this->assertSame('public', $responseData['events'][0]['privacy']);

    }

    public function testURLMiss()
    {

        $this->setupCommon();

        $event = new Event();
        $event->setAccount($this->account);
        $event->setTimezone($this->timezone);
        $event->setCountry($this->country);
        $event->setStartWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $event->setEndWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $event->setTitle('Title');
        $event->setUrl('https://www.theoccasionoctopus.net/');
        $event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(0);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/v1/account/'.$this->account->getId().'/events.json?url=' . urlencode('http://example.com/'));
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertEquals(0, count($responseData['events']));

    }

}

