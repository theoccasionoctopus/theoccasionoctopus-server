<?php

namespace App\Service\UpdateSourcedEvent;

use App\Entity\EventHasSourceEvent;
use App\Entity\Source;
use App\Entity\SourceHasTag;
use App\Service\HistoryWorker\HistoryWorkerService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Sabre\VObject;
use Psr\Log\LoggerInterface;

class UpdateSourcedEventService
{

    /** @var  EntityManagerInterface */
    protected $entityManager;

    protected $historyWorkerService;

    /** @var LoggerInterface  */
    protected $logger;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, HistoryWorkerService $historyWorkerService, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->historyWorkerService = $historyWorkerService;
        $this->logger = $logger;
    }

    public function update(EventHasSourceEvent $eventHasSourceEvent)
    {

        # TODO This should be some check of whether updates from the source are still wanted
        if (true) {

            # TODO if event has passed, don't bother?
            if ($eventHasSourceEvent->getEvent()->copyFromEvent($eventHasSourceEvent->getSourceEvent())) {

                $this->logger->info('Updating event from source event', ['event_id' => $eventHasSourceEvent->getEvent()->getId(), 'account_id'=>$eventHasSourceEvent->getEvent()->getAccount()->getId()]);

                $historyWorker = $this->historyWorkerService->getHistoryWorker($eventHasSourceEvent->getEvent()->getAccount(), null);
                $historyWorker->addEvent($eventHasSourceEvent->getEvent());
                $this->historyWorkerService->persistHistoryWorker($historyWorker);

            }

        }

    }

}

