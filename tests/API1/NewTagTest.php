<?php

namespace App\Tests\API1;

use App\Entity\Account;
use App\Entity\APIAccessToken;
use App\Entity\Country;
use App\Entity\Tag;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Library;
use App\Tests\BaseWebTestWithDataBase;

class NewTagTest extends BaseWebTestWithDataBase
{


    protected $owner;
    protected $country;
    protected $timezone;
    protected $account;
    protected $token;

    private function setupCommon()
    {

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

    public function test1()
    {

        $this->setupCommon();


        $this->client->catchExceptions(false);
        $this->client->request(
            'POST',
            '/api/v1/account/' . $this->account->getId() . '/tag.json',
            [
                'title' => 'TEST TAG',
                'description' => '123',
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

        $tags = $this->entityManager
            ->getRepository(Tag::class)
            ->findAll();

        $this->assertSame(1, count($tags));
        /** @var Event $tag */
        $tag = $tags[0];

        $this->assertSame($tag->getId(), $responseData['tag']['id']);
        $this->assertSame('TEST TAG', $tag->getTitle());
        $this->assertSame('123', $tag->getDescription());
        $this->assertSame('many', $tag->getExtraField('cats'));
        $this->assertSame('zero', $tag->getExtraField('dogs'));


    }


    public function testDuplicate()
    {

        $this->setupCommon();

        # Create once

        $this->client->catchExceptions(false);
        $this->client->request(
            'POST',
            '/api/v1/account/' . $this->account->getId() . '/tag.json',
            [
                'title' => 'TEST',
            ],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer CAT",
            ]
        );
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);


        $tags = $this->entityManager
            ->getRepository(Tag::class)
            ->findAll();

        $this->assertSame(1, count($tags));
        /** @var Event $tag */
        $tag = $tags[0];

        $this->assertSame($tag->getId(), $responseData['tag']['id']);
        $this->assertSame('TEST', $tag->getTitle());

        # Create Twice - should crash

        $this->client->catchExceptions(false);
        $this->client->request(
            'POST',
            '/api/v1/account/' . $this->account->getId() . '/tag.json',
            [
                'title' => 'TEST',
            ],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer CAT",
            ]
        );
        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame('title_already_exists', $responseData['error']['id']);
        $this->assertSame($tag->getId(), $responseData['error']['existing_tag_id']);


    }

}
