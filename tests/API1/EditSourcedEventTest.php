<?php
namespace App\Tests\API1;

use App\Entity\Account;
use App\Entity\APIAccessToken;
use App\Entity\Country;
use App\Entity\Event;
use App\Entity\EventHasSourceEvent;
use App\Entity\EventHasTag;
use App\Entity\Tag;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Library;
use App\Tests\BaseWebTestWithDataBase;

class EditSourcedEventTest extends BaseWebTestWithDataBase
{


    protected $country;
    protected $timezone;

    protected $sourceOwner;
    protected $sourceAccount;
    protected $sourceEvent;

    protected $owner;
    protected $account;
    protected $event;
    
    private function setupCommon() {


        list($this->country, $this->timezone) = $this->createCountryDataForUK();

        list($this->sourceOwner, $this->sourceAccount) = $this->createUserAndAccount('test1', $this->country, $this->timezone);

        $this->sourceEvent = new Event();
        $this->sourceEvent->setAccount($this->sourceAccount);
        $this->sourceEvent->setTimezone($this->timezone);
        $this->sourceEvent->setCountry($this->country);
        $this->sourceEvent->setStartWithObject(new \DateTime('2025-01-01 10:00:00', new \DateTimeZone('UTC')));
        $this->sourceEvent->setEndWithObject(new \DateTime('2025-01-01 11:00:00', new \DateTimeZone('UTC')));
        $this->sourceEvent->setTitle('Title');
        $this->sourceEvent->setId('36573fb9-a021-4005-9fd2-3034cda50a32');
        $this->sourceEvent->setSlug('36573fb9-a021-4005-9fd2-3034cda50a32');
        $this->sourceEvent->setPrivacy(0);
        $this->entityManager->persist($this->sourceEvent);


        list($this->owner, $this->account) = $this->createUserAndAccount('test2', $this->country, $this->timezone);

        $this->event = new Event();
        $this->event->setAccount($this->account);
        $this->event->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $this->event->setSlug('36573fb9-a021-4005-9fd2-3034cda50a72');
        $this->event->setPrivacy(0);
        $this->event->copyFromEvent($this->sourceEvent);
        $this->entityManager->persist($this->event);


        $eventHasSourceEvent = new EventHasSourceEvent();
        $eventHasSourceEvent->setSourceEvent($this->sourceEvent);
        $eventHasSourceEvent->setEvent($this->event);
        $this->entityManager->persist($eventHasSourceEvent);


        $this->token = new APIAccessToken();
        $this->token->setAccount($this->account);
        $this->token->setUser($this->owner);
        $this->token->setEnabled(true);
        $this->token->setWrite(true);
        $this->token->setToken('CAT');
        $this->entityManager->persist($this->token);

        $this->entityManager->flush();

    }
    
    public function testFail() {

        $this->setupCommon();


        $this->client->catchExceptions(false);
        $this->client->request(
            'POST',
            '/api/v1/account/'.$this->account->getId().'/event/36573fb9-a021-4005-9fd2-3034cda50a72.json',
            [
                'title'=> 'TEST CAT',
                'description' => '123',
                'url' => 'https://www.google.com/',
                'url_tickets' => 'https://www.bing.com',
            ],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer CAT",
            ]
        );
        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['changes']);
        $this->assertSame('can_not_edit_field', $responseData['error']['id']);
        $this->assertSame('sourced', $responseData['error']['fields_mode']);
        $this->assertSame([0=>'description',1=>'title',2=>'url',3=>'url_tickets'], $responseData['error']['fields_not_allowed']);


    }


}
