<?php
namespace App\Command;


use App\Entity\Event;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Source;
use App\Import\ImportRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;


class FixEventToEventOccurrenceCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:fix-event-to-event-occurrence';

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
            ->setDescription('Makes Event Occurrence Events from Events (Should happen automatically')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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

}
