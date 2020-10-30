<?php
namespace App\Tests\API;

use App\Library;
use App\Tests\BaseTest;

class WebFingerLibraryTest extends BaseTest {


    public function dataForTestBasic()
    {
        return array(
            array('test1','test1', Null),
            array('acct:test1','test1', Null),
            array('@test1','test1', Null),
            array('@test1@localhost','test1', 'localhost'),
            array('test1@localhost','test1', 'localhost'),
        );
    }


    /**
     * @dataProvider dataForTestBasic
     */
    public function testBasic($in, $expectedUsername, $expectedHost) {
        list($username, $host) = Library::parseWebFingerResourceToUsernameAndHost($in);
        $this->assertSame($expectedUsername, $username);
        $this->assertSame($expectedHost, $host);
    }


}
