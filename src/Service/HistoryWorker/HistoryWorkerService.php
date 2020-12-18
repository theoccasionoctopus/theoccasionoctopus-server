<?php

namespace App\Service\HistoryWorker;

use App\Entity\HistoryHasEventHasTag;
use App\Entity\HistoryHasTag;
use App\Entity\User;
use App\Entity\Account;
use App\Entity\HistoryHasEvent;
use App\SymfonyEvent\HistorySavedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HistoryWorkerService
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


    public function getHistoryWorker(Account $account, User $user = null)
    {
        return new HistoryWorker($account, $user);
    }


    public function persistHistoryWorker(HistoryWorker $historyWorker)
    {
        if (!$historyWorker->hasContents()) {
            return;
        }

        $history = $historyWorker->getHistory();
        $this->entityManager->persist($history);

        foreach ($historyWorker->getEvents() as $event) {
            $this->entityManager->persist($event);

            $historyEvent = new HistoryHasEvent();
            $historyEvent->setHistory($history);
            $historyEvent->setEvent($event);

            $this->entityManager->persist($historyEvent);
        }

        foreach ($historyWorker->getTags() as $tag) {
            $this->entityManager->persist($tag);

            $historyTag = new HistoryHasTag();
            $historyTag->setHistory($history);
            $historyTag->setTag($tag);

            $this->entityManager->persist($historyTag);
        }


        foreach ($historyWorker->getEventHasTags() as $eventHasTag) {
            $this->entityManager->persist($eventHasTag);

            $historyHasEventHasTag = new HistoryHasEventHasTag();
            $historyHasEventHasTag->setEvent($eventHasTag->getEvent());
            $historyHasEventHasTag->setTag($eventHasTag->getTag());
            $historyHasEventHasTag->setHistory($history);

            $this->entityManager->persist($historyHasEventHasTag);
        }

        foreach ($historyWorker->getEventHasSourceEvents() as $eventHasSourceEvent) {
            $this->entityManager->persist($eventHasSourceEvent);

            // We don't currently save anything to link this change to this history, because we don't think we need that information.
            // This may change in future.
            // But in the mean time, we do get other code to pass objects in here so they can be written in the same database transaction as any new events.
        }

        foreach ($historyWorker->getEventHasImports() as $eventHasImport) {
            $this->entityManager->persist($eventHasImport);

            // We don't currently save anything to link this change to this history, because we don't think we need that information.
            // This may change in future.
            // But in the mean time, we do get other code to pass objects in here so they can be written in the same database transaction as any new events.
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new HistorySavedEvent($history), HistorySavedEvent::NAME);
    }
}
