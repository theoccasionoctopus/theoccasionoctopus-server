<?php

namespace App\Tests;

use App\Library;
use PHPUnit\Framework\TestCase;

class LibraryMakeAccountUsernameCanonicalTest extends TestCase {

    public function  test1() {
        $this->assertEquals("test", Library::makeAccountUsernameCanonical("TEST"));
    }

}
