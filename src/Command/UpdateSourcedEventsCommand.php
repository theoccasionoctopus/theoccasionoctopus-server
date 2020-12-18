<?php

namespace App\Command;

use App\Entity\EventHasSourceEvent;
use App\Service\UpdateSourcedEvent\UpdateSourcedEventService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Entity\Source;
use GuzzleHttp\Client;
use App\Import\ImportRunner;
use Twig\Environment;

class UpdateSourcedEventsCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:update-sourced-events-command';

    /** @var  UpdateSourcedEventService */
    protected $updateSourcedEventService;

    /**
     * LoadCountryData constructor.
     */
    public function __construct(ContainerInterface $container, UpdateSourcedEventService $updateSourcedEventService)
    {
        parent::__construct();
        $this->container = $container;
        $this->updateSourcedEventService = $updateSourcedEventService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update Sourced Events')
            ->setHelp('Update Sourced Events');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->container->get('doctrine');
        /** @var EventHasSourceEvent $eventHasSourceEvent */
        foreach ($doctrine->getRepository(EventHasSourceEvent::class)->findAll() as $eventHasSourceEvent) {
            $this->updateSourcedEventService->update($eventHasSourceEvent);
        }
        return 0;
    }
}
