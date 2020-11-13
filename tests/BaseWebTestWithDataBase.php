<?php

namespace App\Tests;


use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\Country;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Entity\UserManageAccount;
use App\Library;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;



/**
 */
abstract class BaseWebTestWithDataBase extends WebTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    protected $client;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {

        $this->client = self::createClient();

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

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

    protected function createCountryDataForUK(): array
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

    protected  function createUserAndAccount($name, $country, $timezone): array {

        $user = new User();
        $user->setEmail($name."@example.com");
        $user->setTitle($name);
        $user->setPassword('1234');
        $this->entityManager->persist($user);

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
        $accountLocal->setSEOIndexFollow(true);
        $this->entityManager->persist($accountLocal);

        $userManageAccount = new UserManageAccount();
        $userManageAccount->setAccount($account);
        $userManageAccount->setUser($user);
        $this->entityManager->persist($userManageAccount);

        $this->entityManager->flush();

        return [ $user, $account ];

    }
}
