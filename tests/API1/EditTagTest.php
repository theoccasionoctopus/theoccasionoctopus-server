<?php
namespace App\Tests\API1;

use App\Entity\Account;
use App\Entity\APIAccessToken;
use App\Entity\Country;
use App\Entity\Event;
use App\Entity\EventHasTag;
use App\Entity\Tag;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Library;
use App\Tests\BaseWebTestWithDataBase;

class EditTagTest extends BaseWebTestWithDataBase
{

    
    protected $owner;
    protected $country;
    protected $timezone;
    protected $account;
    protected $event;
    
    private function setupCommon() {

        list($this->country, $this->timezone) = $this->createCountryDataForUK();
        list($this->owner, $this->account) = $this->createUserAndAccount('test1', $this->country, $this->timezone);

        $this->tag = new Tag();
        $this->tag->setAccount($this->account);
        $this->tag->setTitle('Title');
        $this->tag->setId('36573fb9-a021-4005-9fd2-3034cda50a72');
        $this->tag->setPrivacy(0);
        $this->tag->setEnabled(True);
        $this->entityManager->persist($this->tag);


        $this->token = new APIAccessToken();
        $this->token->setAccount($this->account);
        $this->token->setUser($this->owner);
        $this->token->setEnabled(true);
        $this->token->setWrite(true);
        $this->token->setToken('CAT');
        $this->entityManager->persist($this->token);

        $this->entityManager->flush();

    }
    
    public function testBasic() {

        $this->setupCommon();


        $this->client->catchExceptions(false);
        $this->client->request(
            'POST',
            '/api/v1/account/'.$this->account->getId().'/tag/36573fb9-a021-4005-9fd2-3034cda50a72.json',
            [
                'title'=> 'TEST CAT',
                'description' => '123',
            ],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer CAT",
            ]
        );
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['changes']);

        $this->entityManager->clear();
        $tags = $this->entityManager
            ->getRepository(Tag::class)
            ->findAll()
        ;

        $this->assertSame(1, count($tags));
        /** @var Tag $tag */
        $tag = $tags[0];

        $this->assertSame($tag->getId(), $responseData['tag']['id']);
        $this->assertSame('TEST CAT', $tag->getTitle());
        $this->assertSame('123', $tag->getDescription());
        

    }

    public function testExtra() {

        $this->setupCommon();


        $this->client->catchExceptions(false);
        $this->client->request(
            'POST',
            '/api/v1/account/'.$this->account->getId().'/tag/36573fb9-a021-4005-9fd2-3034cda50a72.json',
            [
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
        $this->assertTrue($responseData['changes']);

        $this->entityManager->clear();
        $tags = $this->entityManager
            ->getRepository(Tag::class)
            ->findAll()
        ;

        $this->assertSame(1, count($tags));
        /** @var Tag $tag */
        $tag = $tags[0];

        $this->assertSame($tag->getId(), $responseData['tag']['id']);
        $this->assertSame('Title', $tag->getTitle());
        $this->assertSame('many', $tag->getExtraField('cats'));
        $this->assertSame('zero', $tag->getExtraField('dogs'));

    }
    

}
