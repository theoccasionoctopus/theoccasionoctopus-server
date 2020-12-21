<?php

namespace App\Tests;

use App\Library;
use PHPUnit\Framework\TestCase;

class LibraryGetActivityStreamsActorURLFromWebFingerData extends TestCase {

    public function  test1() {

        $data = json_decode("{\"subject\":\"acct:testone@localhost:8080\",\"aliases\":[\"http:\/\/localhost:8080\/a\/testone\"],\"links\":[{\"rel\":\"http:\/\/webfinger.net\/rel\/profile-page\",\"type\":\"text\/html\",\"href\":\"http:\/\/localhost:8080\/a\/testone\"},{\"rel\":\"self\",\"type\":\"application\/activity+json\",\"href\":\"http:\/\/localhost:8080\/activitystreams\/4909fba6-e822-4c44-aa89-965c95a85562\"},{\"rel\":\"self\",\"type\":\"application\/activity+json\",\"href\":\"http:\/\/localhost:8080\/a\/testone\"}],\"occasion-octopus-id\":\"4909fba6-e822-4c44-aa89-965c95a85562\",\"occasion-octopus-title\":\"Test One\",\"occasion-octopus-username\":\"testone\"}", true);

        $this->assertEquals(
            "http://localhost:8080/activitystreams/4909fba6-e822-4c44-aa89-965c95a85562",
            Library::getActivityStreamsActorURLFromWebFingerData(
                $data
            ));
    }

}
