<?php

namespace App\EventSubscriber;

use App\Entity\HistoryHasEvent;
use App\SymfonyEvent\HistorySavedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Service\EventToEventOccurrence\EventToEventOccurrenceService;
use Doctrine\ORM\EntityManagerInterface;

class HistorySavedEventSubscriber implements EventSubscriberInterface
{

    /** @var  EventToEventOccurrenceService */
    protected $eventToEventOccurrenceService;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /**
     * @param EventToEventOccurrenceService $eventToEventOccurrenceService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EventToEventOccurrenceService $eventToEventOccurrenceService, EntityManagerInterface $entityManager)
    {
        $this->eventToEventOccurrenceService = $eventToEventOccurrenceService;
        $this->entityManager = $entityManager;
    }


    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            HistorySavedEvent::NAME => [
                ['process', 10],
            ],
        ];
    }

    public function process(HistorySavedEvent $historySavedEvent)
    {
        foreach ($this->entityManager->getRepository(HistoryHasEvent::class)->findByHistory($historySavedEvent->getHistory()) as $historyHasEvent) {
            $this->eventToEventOccurrenceService->process($historyHasEvent->getEvent());
        }
    }
}
