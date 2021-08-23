<?php
namespace App\Tests\API1;

use App\Constants;
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

    public function testListPublic()
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
        $event->setSlug('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(Constants::PRIVACY_LEVEL_PUBLIC);

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

    public function testListOnlyFollowers()
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
        $event->setSlug('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(Constants::PRIVACY_LEVEL_ONLY_FOLLOWERS);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/v1/account/'.$this->account->getId().'/events.json');
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(0, count($responseData['events']));

    }

    public function testListOnlyFollowersWithCorrectToken()
    {
        $this->setupCommon();

        list($followerUser, $followerAccount) = $this->createUserAndAccountThatFollowsOtherAccount('testFollower', $this->country, $this->timezone, $this->account);

        $token = new APIAccessToken();
        // TODO when we change what binding an token to an account means, we should be able to put this back in
        // $token->setAccount($followerAccount);
        $token->setUser($followerUser);
        $token->setEnabled(true);
        $token->setToken('CAT');
        $this->entityManager->persist($token);

        $event = new Event();
        $event->setAccount($this->account);
        $event->setTimezone($this->timezone);
        $event->setCountry($this->country);
        $event->setStartWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $event->setEndWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $event->setTitle('Title');
        $event->setUrl('https://www.theoccasionoctopus.net/');
        $event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setSlug('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(Constants::PRIVACY_LEVEL_ONLY_FOLLOWERS);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/v1/account/'.$this->account->getId().'/events.json?access_token=CAT');
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(1, count($responseData['events']));
        $this->assertSame('Title', $responseData['events'][0]['title']);
        $this->assertSame('only-followers', $responseData['events'][0]['privacy']);

    }

    public function testListPrivate()
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
        $event->setSlug('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(Constants::PRIVACY_LEVEL_PRIVATE);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/v1/account/'.$this->account->getId().'/events.json');
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(0, count($responseData['events']));

    }

    public function testListPrivateWithCorrectToken()
    {

        $this->setupCommon();


        $token = new APIAccessToken();
        $token->setAccount($this->account);
        $token->setUser($this->owner);
        $token->setEnabled(true);
        $token->setToken('CAT');
        $this->entityManager->persist($token);

        $event = new Event();
        $event->setAccount($this->account);
        $event->setTimezone($this->timezone);
        $event->setCountry($this->country);
        $event->setStartWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $event->setEndWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $event->setTitle('Title');
        $event->setUrl('https://www.theoccasionoctopus.net/');
        $event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setSlug('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(Constants::PRIVACY_LEVEL_PRIVATE);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/v1/account/'.$this->account->getId().'/events.json?access_token=CAT');
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(1, count($responseData['events']));
        $this->assertSame('Title', $responseData['events'][0]['title']);
        $this->assertSame('private', $responseData['events'][0]['privacy']);

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
        $event->setSlug('36573fb9-a021-4005-9fd2-3034cda50a72');
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
        $event->setSlug('36573fb9-a021-4005-9fd2-3034cda50a72');
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

