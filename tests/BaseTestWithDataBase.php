<?php

namespace App\Tests;

use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\Country;
use App\Entity\TimeZone;
use App\Entity\UserManageAccount;
use App\Library;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;



/**
 */
abstract class BaseTestWithDataBase extends KernelTestCase
{

    protected $doctrine;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $kernel = self::bootKernel();

        $this->doctrine = $kernel->getContainer()->get('doctrine');
        $this->entityManager = $this->doctrine->getManager();

        $consoleApplication = new Application(static::$kernel);
        $consoleApplication->setAutoExit(false);
        $consoleApplication->run(new StringInput('doctrine:schema:drop --force --quiet'));
        $consoleApplication->run(new StringInput('doctrine:migrations:version  --no-interaction --delete --all --quiet'));

        $consoleApplication->run(new StringInput('doctrine:migrations:migrate --no-interaction --quiet'));
    }


    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null; // avoid memory leaks
        }
    }

    protected function createCountryDataForUK()
    {

        $country = new Country();
        $country->setTitle("United Kingdom of Great Britain and Northern Ireland (the");
        $country->setIso3166TwoChar("GB");
        $this->entityManager->persist($country);

        $timezone = new TimeZone();
        $timezone->setTitle("Europe/London");
        $timezone->setCode("Europe/London");
        $this->entityManager->persist($timezone);

        $this->entityManager->flush();

        return [ $country, $timezone ];

    }

    protected  function createAccount($name, $country, $timezone): Account {


        $account = new Account();
        $account->setId(Library::GUID());
        $account->setTitle($name);
        $this->entityManager->persist($account);

        $accountLocal = new AccountLocal();
        $accountLocal->setAccount($account);
        $accountLocal->setUsername($name);
        $accountLocal->setDefaultTimezone($timezone);
        $accountLocal->setDefaultCountry($country);
        $accountLocal->setDefaultPrivacy(0);
        $this->entityManager->persist($accountLocal);

        $this->entityManager->flush();

        return $account;

    }

}
