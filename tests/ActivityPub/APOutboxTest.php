<?php
namespace App\Tests\ActivityPub;

use App\ActivityPub\APOutbox;
use App\Tests\BaseTest;

class APOutboxTest extends BaseTest
{

    public function testMastodonFirstPage() {

        $apoutbox = new APOutbox(
            [
                'type'=> 'OrderedCollection',
                'first'=> 'http://example.com/?page=1',
            ],
            "http://example.com"
        );

        $items = $apoutbox->getItems();
        $this->assertSame(0, count($items));
        $this->assertsame("http://example.com/?page=1", $apoutbox->getNextURL());

    }

    public function testMastodonSecondPage() {

        $apoutbox = new APOutbox(
            [
                'type'=> 'OrderedCollectionPage',
                'next'=> 'http://example.com/?page=2',
                'orderedItems' => [
                    [ ]
                ]
            ],
            "http://example.com/?page=1"
        );

        $items = $apoutbox->getItems();
        $this->assertSame(1, count($items));
        $this->assertsame("http://example.com/?page=2", $apoutbox->getNextURL());

    }

    public function testMobilizonFirstPage() {

        $apoutbox = new APOutbox(
            [
                'type'=> 'OrderedCollection',
                'first'=> [
                    'type'=> 'OrderedCollectionPage',
                    'orderedItems' => [
                        [ ]
                    ]
                ],
            ],
            "http://example.com"
        );

        $items = $apoutbox->getItems();
        $this->assertSame(1, count($items));
        $this->assertsame(null, $apoutbox->getNextURL());

    }


}

