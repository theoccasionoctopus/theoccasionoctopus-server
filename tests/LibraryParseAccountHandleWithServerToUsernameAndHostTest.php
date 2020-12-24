<?php

namespace App\Tests;

use App\Library;
use PHPUnit\Framework\TestCase;

class LibraryParseAccountHandleWithServerToUsernameAndHostTest extends TestCase {

    public function test1() {
        list($username, $hostname) = Library::parseAccountHandleWithServerToUsernameAndHost('@cat@demo.com');
        $this->assertSame('cat', $username);
        $this->assertSame('demo.com', $hostname);
    }


    public function test2() {
        list($username, $hostname) = Library::parseAccountHandleWithServerToUsernameAndHost('cat@demo.com');
        $this->assertSame('cat', $username);
        $this->assertSame('demo.com', $hostname);
    }

}
