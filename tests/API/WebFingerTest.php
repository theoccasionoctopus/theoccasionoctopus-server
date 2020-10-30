<?php
namespace App\Tests\API;

use App\Entity\Account;
use App\Entity\APIAccessToken;
use App\Entity\Country;
use App\Entity\Event;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Library;
use App\Tests\BaseWebTestWithDataBase;

class WebFingerTest extends BaseWebTestWithDataBase
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


    public function testBasic() {

        $this->setupCommon();

        $this->client->catchExceptions(false);
        $this->client->request(
            'GET',
            '/.well-known/webfinger?resource=test1',
        );
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertSame($this->account->getId(), $responseData['occasion-octopus-id']);
        $this->assertSame('test1', $responseData['occasion-octopus-title']);

    }

}