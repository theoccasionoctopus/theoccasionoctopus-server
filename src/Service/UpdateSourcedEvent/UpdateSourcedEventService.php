<?php

namespace App\Service\UpdateSourcedEvent;

use App\Entity\EventHasSourceEvent;
use App\Entity\Source;
use App\Entity\SourceHasTag;
use App\Service\HistoryWorker\HistoryWorkerService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Sabre\VObject;

class UpdateSourcedEventService
{

    /** @var  EntityManagerInterface */
    protected $entityManager;

    protected $historyWorkerService;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, HistoryWorkerService $historyWorkerService)
    {
        $this->entityManager = $entityManager;
        $this->historyWorkerService = $historyWorkerService;
    }

    public function update(EventHasSourceEvent $eventHasSourceEvent)
    {

        # TODO This should be some check of whether updates from the source are still wanted
        if (true) {

            # TODO if event has passed, don't bother?
            if ($eventHasSourceEvent->getEvent()->copyFromEvent($eventHasSourceEvent->getSourceEvent())) {

                $historyWorker = $this->historyWorkerService->getHistoryWorker($eventHasSourceEvent->getEvent()->getAccount(), null);
                $historyWorker->addEvent($eventHasSourceEvent->getEvent());
                $this->historyWorkerService->persistHistoryWorker($historyWorker);

            }

        }

    }

}

