<?php

namespace App\Command;

use App\Entity\Event;
use App\Service\Purge\PurgeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Entity\Source;
use GuzzleHttp\Client;
use App\Import\ImportRunner;
use Twig\Environment;

class PurgeEventCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:purge-event';

    protected $container;

    /** @var  PurgeService */
    protected $purgeService;

    /**
     * LoadCountryData constructor.
     */
    public function __construct(ContainerInterface $container, PurgeService $purgeService)
    {
        parent::__construct();
        $this->container = $container;
        $this->purgeService = $purgeService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Purge Event')
            ->setHelp('Purge Event')
            ->addArgument('eventid', InputArgument::REQUIRED, 'Event Id (a GUID string)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->container->get('doctrine');

        $event = $doctrine->getRepository(Event::class)->findOneById($input->getArgument('eventid'));
        if (!$event) {
            $output->writeln('Can not find event');
            return 1;
        }

        $this->purgeService->purgeEvent($event);

        return 0;
    }
}
