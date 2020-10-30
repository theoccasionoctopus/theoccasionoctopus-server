<?php
namespace App\Command;

use App\Entity\Country;
use App\Entity\CountryHasTimeZone;
use App\Entity\TimeZone;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Source;
use App\Import\ImportRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCountryData extends Command
{
    protected static $defaultName = 'theocasionoctupus:load-country-data';

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
            ->setDescription('Load Country Data')
            ->setHelp('Load Country Data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadCountryTitles();
        $this->loadCountryTmeZones();
        return 0;
    }

    protected function loadCountryTitles() {
        $guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'Prototype Software') )));
        $response = $guzzle->request("GET", "https://raw.githubusercontent.com/eggert/tz/master/iso3166.tab", array());
        if ($response->getStatusCode() != 200) {
            throw new Exception("Got Status " . $response->getStatusCode());
        }

        $doctrine = $this->container->get('doctrine');
        $repository = $doctrine->getRepository(Country::class);

        foreach(explode("\n", $response->getBody(true)) as $line) {
            if ($line && substr($line, 0,1) != '#') {
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

    protected function loadCountryTmeZones() {
        $guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'Prototype Software') )));
        $response = $guzzle->request("GET", "https://raw.githubusercontent.com/eggert/tz/master/zone.tab", array());
        if ($response->getStatusCode() != 200) {
            throw new Exception("Got Status " . $response->getStatusCode());
        }

        $doctrine = $this->container->get('doctrine');
        $countryRepository = $doctrine->getRepository(Country::class);
        $timeZoneRepository = $doctrine->getRepository(TimeZone::class);
        $countryUsesTimeZoneRepository = $doctrine->getRepository(CountryHasTimeZone::class);

        foreach(explode("\n", $response->getBody(true)) as $line) {
            if ($line && substr($line, 0,1) != '#') {
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

}