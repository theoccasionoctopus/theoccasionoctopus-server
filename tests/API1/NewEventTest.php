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

class NewEventTest extends BaseWebTestWithDataBase
{

    
    protected $owner;
    protected $country;
    protected $timezone;
    protected $account;
    protected $token;
    
    private function setupCommon() {

        list($this->country, $this->timezone) = $this->createCountryDataForUK();
        list($this->owner, $this->account) = $this->createUserAndAccount('test1', $this->country, $this->timezone);

        $this->token = new APIAccessToken();
        $this->token->setAccount($this->account);
        $this->token->setUser($this->owner);
        $this->token->setEnabled(true);
        $this->token->setWrite(true);
        $this->token->setToken('CAT');

        $this->entityManager->persist($this->token);
        $this->entityManager->flush();
    }
    
    public function testUTC1() {

        $this->setupCommon();


        $this->client->catchExceptions(false);
        $this->client->request(
            'POST',
            '/api/v1/account/'.$this->account->getId().'/event.json',
            [
                'title'=> 'TEST CAT',
                'description' => '123',
                'url' => 'https://www.google.com/',
                'url_tickets' => 'https://www.bing.com',
                'start_year_utc' => 2024,
                'start_month_utc' => 6,
                'start_day_utc' => 1,
                'start_hour_utc' => 13,
                'start_minute_utc' => 45,
                'end_year_utc' => 2025,
                'end_month_utc' => 6,
                'end_day_utc' => 2,
                'end_hour_utc' => 14,
                'end_minute_utc' => 47,
                'extra_field_0_name' => 'cats',
                'extra_field_0_value' => 'many',
                'extra_field_1_name' => 'dogs',
                'extra_field_1_value' => 'zero',
            ],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer CAT",
            ]
        );
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $events = $this->entityManager
            ->getRepository(Event::class)
            ->findAll()
        ;

        $this->assertSame(1, count($events));
        /** @var Event $event */
        $event = $events[0];

        $this->assertSame($event->getId(), $responseData['event']['id']);
        $this->assertSame('TEST CAT', $event->getTitle());
        $this->assertSame('123', $event->getDescription());
        $this->assertSame('https://www.google.com/', $event->getUrl());
        $this->assertSame('https://www.bing.com', $event->getUrlTickets());
        $this->assertSame('2024-06-01T13:45:00+00:00', $event->getStart('UTC')->format('c'));
        $this->assertSame('2025-06-02T14:47:00+00:00', $event->getEnd('UTC')->format('c'));
        $this->assertSame('2024-06-01T14:45:00+01:00', $event->getStart()->format('c'));
        $this->assertSame('2025-06-02T15:47:00+01:00', $event->getEnd()->format('c'));
        $this->assertSame('many', $event->getExtraField('cats'));
        $this->assertSame('zero', $event->getExtraField('dogs'));


    }

    public function testTimezone1() {

        $this->setupCommon();


        $this->client->catchExceptions(false);
        $this->client->request(
            'POST',
            '/api/v1/account/'.$this->account->getId().'/event.json',
            [
                'title'=> 'TEST CAT',
                'description' => '123',
                'url' => 'https://www.google.com/',
                'url_tickets' => 'https://www.bing.com',
                'start_year_timezone' => 2024,
                'start_month_timezone' => 6,
                'start_day_timezone' => 1,
                'start_hour_timezone' => 14,
                'start_minute_timezone' => 45,
                'end_year_timezone' => 2025,
                'end_month_timezone' => 6,
                'end_day_timezone' => 2,
                'end_hour_timezone' => 15,
                'end_minute_timezone' => 47,
                'extra_field_0_name' => 'cats',
                'extra_field_0_value' => 'many',
                'extra_field_1_name' => 'dogs',
                'extra_field_1_value' => 'zero',
            ],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer CAT",
            ]
        );
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $events = $this->entityManager
            ->getRepository(Event::class)
            ->findAll()
        ;

        $this->assertSame(1, count($events));
        /** @var Event $event */
        $event = $events[0];

        $this->assertSame($event->getId(), $responseData['event']['id']);
        $this->assertSame('TEST CAT', $event->getTitle());
        $this->assertSame('123', $event->getDescription());
        $this->assertSame('https://www.google.com/', $event->getUrl());
        $this->assertSame('https://www.bing.com', $event->getUrlTickets());
        $this->assertSame('2024-06-01T13:45:00+00:00', $event->getStart('UTC')->format('c'));
        $this->assertSame('2025-06-02T14:47:00+00:00', $event->getEnd('UTC')->format('c'));
        $this->assertSame('2024-06-01T14:45:00+01:00', $event->getStart()->format('c'));
        $this->assertSame('2025-06-02T15:47:00+01:00', $event->getEnd()->format('c'));
        $this->assertSame('many', $event->getExtraField('cats'));
        $this->assertSame('zero', $event->getExtraField('dogs'));


    }



    public function testAllDay1() {

        $this->setupCommon();


        $this->client->catchExceptions(false);
        $this->client->request(
            'POST',
            '/api/v1/account/'.$this->account->getId().'/event.json',
            [
                'title'=> 'TEST CAT',
                'all_day'=>True,
                'start_year' => 2024,
                'start_month' => 6,
                'start_day' => 1,
                'end_year' => 2025,
                'end_month' => 6,
                'end_day' => 2,
            ],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer CAT",
            ]
        );
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $events = $this->entityManager
            ->getRepository(Event::class)
            ->findAll()
        ;

        $this->assertSame(1, count($events));
        /** @var Event $event */
        $event = $events[0];

        $this->assertSame($event->getId(), $responseData['event']['id']);
        $this->assertSame('TEST CAT', $event->getTitle());
        $this->assertSame(True, $event->isAllDay());
        $this->assertSame('2024-06-01T00:00:00+01:00', $event->getStart()->format('c'));
        $this->assertSame('2025-06-02T23:59:59+01:00', $event->getEnd()->format('c'));


    }


}