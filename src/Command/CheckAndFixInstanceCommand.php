<?php
namespace App\Command;

use App\Entity\AccountLocal;
use App\Entity\Event;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Source;
use App\Import\ImportRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Entity\Country;
use App\Entity\CountryHasTimeZone;
use App\Entity\TimeZone;
use GuzzleHttp\Client;

class CheckAndFixInstanceCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:check-and-fix-instance';

    /** @var  ContainerInterface */
    protected $container;

    /**
     * LoadCountryData constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setDescription('Checks and fixes any issues found. Run after install/upgrading or if problems seen.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Country Titles");
        $this->loadCountryTitles();
        $output->writeln("Country Time Zones");
        $this->loadCountryTmeZones();
        $output->writeln("All Local Users Have Keys");
        $this->dataMigrationAllLocalUsersHaveKeys($output);
        $output->writeln("Event to Event Occurrences");
        $this->eventToEventOccurrenceFixer($output);
        return 0;
    }

    protected function eventToEventOccurrenceFixer(OutputInterface $output)
    {
        $doctrine = $this->container->get('doctrine');
        $eventRepository = $doctrine->getRepository(Event::class);
        $service = $this->container->get('app.eventToEventOccurrenceService');

        foreach ($eventRepository->findBy([]) as $event) {
            $output->writeln("Event " . $event->getId());
            $service->process($event);
        }

        return 0;
    }


    protected function loadCountryTitles()
    {
        $guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'Prototype Software') )));
        $response = $guzzle->request("GET", "https://raw.githubusercontent.com/eggert/tz/master/iso3166.tab", array());
        if ($response->getStatusCode() != 200) {
            throw new Exception("Got Status " . $response->getStatusCode());
        }

        $doctrine = $this->container->get('doctrine');
        $repository = $doctrine->getRepository(Country::class);

        foreach (explode("\n", $response->getBody(true)) as $line) {
            if ($line && substr($line, 0, 1) != '#') {
                $bits = explode("\t", $line) ;

                $country = $repository->findOneBy(array('iso3166_two_char'=>$bits[0]));
                if (!$country) {
                    $country = new Country();
                    $country->setIso3166TwoChar($bits[0]);
                }
                $country->setTitle($bits[1]);
                $doctrine->getManager()->persist($country);
                $doctrine->getManager()->flush();
            }
        }
    }

    protected function loadCountryTmeZones()
    {
        $guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'Prototype Software') )));
        $response = $guzzle->request("GET", "https://raw.githubusercontent.com/eggert/tz/master/zone.tab", array());
        if ($response->getStatusCode() != 200) {
            throw new \Exception("Got Status " . $response->getStatusCode());
        }

        $doctrine = $this->container->get('doctrine');
        $countryRepository = $doctrine->getRepository(Country::class);
        $timeZoneRepository = $doctrine->getRepository(TimeZone::class);
        $countryUsesTimeZoneRepository = $doctrine->getRepository(CountryHasTimeZone::class);

        foreach (explode("\n", $response->getBody(true)) as $line) {
            if ($line && substr($line, 0, 1) != '#') {
                $bits = explode("\t", $line) ;

                $country = $countryRepository->findOneBy(array('iso3166_two_char'=>$bits[0]));
                if (!$country) {
                    throw new \Exception('Can not load country');
                }

                $timezone = $timeZoneRepository->findOneByCode($bits[2]);
                if (!$timezone) {
                    $timezone = new TimeZone();
                    $timezone->setTitle($bits[2]);
                    $timezone->setCode($bits[2]);
                    $doctrine->getManager()->persist($timezone);
                    $doctrine->getManager()->flush();
                }

                $countryUsesTimeZone = $countryUsesTimeZoneRepository->findOneBy(array('country'=>$country, 'timezone'=>$timezone));
                if (!$countryUsesTimeZone) {
                    $countryUsesTimeZone = new CountryHasTimeZone();
                    $countryUsesTimeZone->setCountry($country);
                    $countryUsesTimeZone->setTimezone($timezone);
                    $doctrine->getManager()->persist($countryUsesTimeZone);
                    $doctrine->getManager()->flush();
                }
            }
        }

        # TODO do something to remove old CountryHasTimeZone links that don't apply any more.
    }

    protected function dataMigrationAllLocalUsersHaveKeys(OutputInterface $output)
    {
        $doctrine = $this->container->get('doctrine');
        /** @var AccountLocal $accountLocal */
        foreach ($doctrine->getRepository(AccountLocal::class)->findBy([]) as $accountLocal) {
            if (!$accountLocal->getKeyPublic()) {
                $output->writeln("Account " . $accountLocal->getAccount()->getId());
                $accountLocal->generateNewKey();
                $doctrine->getManager()->persist($accountLocal);
                $doctrine->getManager()->flush();
            }
        }
        return 0;
    }
}
