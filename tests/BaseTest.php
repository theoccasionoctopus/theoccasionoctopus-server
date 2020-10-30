<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;



/**
 */
abstract class BaseTest extends KernelTestCase
{




    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

    }



    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

    }

}
