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

/**
 * Class UpdateSourcedEventsCommand
 * @package App\Command
 *
 * There are 2 cases where we need to this
 *
 * Case 1) When a local user has sourced an event from another local user
 * In this case, there is now a message to update straight away.
 * This cron is not needed in this case.
 *
 * Case 2) When a local user has sourced an event from a remote user
 * In this case, there is no automatic update, so we still need a regular cron job
 */
class UpdateSourcedEventsCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:update-sourced-events-command';

    /** @var  UpdateSourcedEventService */
    protected $updateSourcedEventService;

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
