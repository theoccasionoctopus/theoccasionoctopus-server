<?php

namespace App\Service\EventToEventOccurrence;

use App\Entity\Event;
use App\Entity\EventOccurrence;
use App\Library;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventToEventOccurrenceService
{



    /** @var  EntityManagerInterface */
    protected $entityManager;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * HistoryWorker constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function process(Event $event)
    {
        $occurrenceResults = $this->getOccurrenceResultsForEvent($event);
        $occurrenceResultsByUTCStart = [];
        foreach ($occurrenceResults as $occurrenceResult) {
            $occurrenceResultsByUTCStart[$occurrenceResult->getStartUTC()->format('c')] = $occurrenceResult;
        }

        $eventOccurrenceRepository = $this->entityManager->getRepository(EventOccurrence::class);
        $eventOccurrences = $eventOccurrenceRepository->findByEvent($event);
        $eventOccurrencesByUTCStart = [];
        foreach ($eventOccurrences as $eventOccurrence) {
            $eventOccurrencesByUTCStart[$eventOccurrence->getStart('UTC')->format('c')] = $eventOccurrence;
        }

        # Look for ones that exactly match, take them out - nothing to process
        foreach (array_keys($occurrenceResultsByUTCStart) as $key) {
            if (array_key_exists($key, $occurrenceResultsByUTCStart) && array_key_exists($key, $eventOccurrencesByUTCStart)) {
                # But the end might have changed - so reset the end!
                $eventOccurrencesByUTCStart[$key]->setEndWithObject($occurrenceResultsByUTCStart[$key]->getEndUTC());
                $this->entityManager->persist($eventOccurrencesByUTCStart[$key]);
                # And then remove from any further consideration
                unset($occurrenceResultsByUTCStart[$key]);
                unset($eventOccurrencesByUTCStart[$key]);
            }
        }

        # Look for ones that are close, move them
        # TODO

        # Any to create?
        /** @var EventOccurrenceResult $occurrenceResult */
        foreach ($occurrenceResultsByUTCStart as $occurrenceResult) {
            $eventOccurrence = new EventOccurrence();
            $eventOccurrence->setEvent($event);
            $eventOccurrence->setId(Library::GUID());
            $eventOccurrence->setStartWithObject($occurrenceResult->getStartUTC());
            $eventOccurrence->setEndWithObject($occurrenceResult->getEndUTC());
            $this->entityManager->persist($eventOccurrence);
        }

        # Any to delete?
        foreach ($eventOccurrencesByUTCStart as $eventOccurrence) {
            $this->entityManager->remove($eventOccurrence);
        }

        # Finish up
        $this->entityManager->flush();
    }

    protected function getOccurrenceResultsForEvent(Event $event)
    {
        if ($event->getRrule()) {
            $rule        = new \Recurr\Rule(
                $event->getRrule(),
                $event->getStartAtTimeZone(),
                $event->getEndAtTimeZone(),
                $event->getTimezone()->getCode()
            );

            $transformer = new \Recurr\Transformer\ArrayTransformer();

            // TODO make some constraits for account years ahead and behind, apply here
            // $constraint = new \Recurr\Transformer\Constraint\BetweenConstraint();

            $out = [];
            foreach ($transformer->transform($rule) as $r) {
                $startUTC = clone $r->getStart();
                $startUTC->setTimezone(new \DateTimeZone('UTC'));
                $endUTC = clone $r->getEnd();
                $endUTC->setTimezone(new \DateTimeZone('UTC'));
                $out[] = new EventOccurrenceResult($startUTC, $endUTC);
            }
            return $out;
        } else {
            return [
                new EventOccurrenceResult($event->getStart('UTC'), $event->getEnd('UTC'))
            ];
        }
    }
}
