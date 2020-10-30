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

class ShowEventTest extends BaseWebTestWithDataBase
{

    
    protected $owner;
    protected $country;
    protected $timezone;
    protected $account;
    
    private function setupCommon() {

        list($this->country, $this->timezone) = $this->createCountryDataForUK();
        list($this->owner, $this->account) = $this->createUserAndAccount('test1', $this->country, $this->timezone);
        
    }
    
    public function testShowPublic() {

        $this->setupCommon();

        $event = new Event();
        $event->setAccount($this->account);
        $event->setTimezone($this->timezone);
        $event->setCountry($this->country);
        $event->setStartWithInts(2025, 1,1, 10, 0, 0);
        $event->setEndWithInts(2025, 1, 1, 10, 0, 0);
        $event->setTitle('Title');
        $event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(0);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/v1/account/'.$this->account->getId().'/event/36573fb9-a021-4005-9fd2-3034cda50a72.json');
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame('Title', $responseData['event']['title']);
        $this->assertSame('public', $responseData['event']['privacy']);
    }

    public function testShowPrivate() {

        $this->setupCommon();

        $event = new Event();
        $event->setAccount($this->account);
        $event->setTimezone($this->timezone);
        $event->setCountry($this->country);
        $event->setStartWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('Europe/London')));
        $event->setEndWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('Europe/London')));
        $event->setTitle('Title');
        $event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(10000);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/v1/account/'.$this->account->getId().'/event/36573fb9-a021-4005-9fd2-3034cda50a72.json');
        $response = $this->client->getResponse();
        $this->assertSame(404, $response->getStatusCode());

    }

    public function testShowPrivateWithCorrectToken() {

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
        $event->setStartWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('Europe/London')));
        $event->setEndWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('Europe/London')));
        $event->setTitle('Title');
        $event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $event->setPrivacy(10000);
        $this->entityManager->persist($event);

        $this->entityManager->flush();

        ############################ TEST 1 - with GET param, hacky way
        $this->client->request(
            'GET',
            '/api/v1/account/'.$this->account->getId().'/event/36573fb9-a021-4005-9fd2-3034cda50a72.json' .
            '?access_token=CAT'
        );
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame('Title', $responseData['event']['title']);
        $this->assertSame('private', $responseData['event']['privacy']);

        ############################ TEST 2 - with Header param, proper way
        $this->client->request(
            'GET',
            '/api/v1/account/'.$this->account->getId().'/event/36573fb9-a021-4005-9fd2-3034cda50a72.json',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer CAT",
            ]
        );
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame('Title', $responseData['event']['title']);
        $this->assertSame('private', $responseData['event']['privacy']);
    }

}