<?php

namespace App\MessageHandler;

use App\Entity\EventHasSourceEvent;
use App\Entity\History;
use App\Entity\Import;
use App\Message\NewHistoryMessage;
use App\Service\Import\ImportService;
use App\Service\UpdateSourcedEvent\UpdateSourcedEventService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

class NewHistoryMessageHandler implements MessageHandlerInterface
{


    /** @var UpdateSourcedEventService */
    protected $updateSourcedEventService;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager, UpdateSourcedEventService $updateSourcedEventService)
    {
        $this->entityManager = $entityManager;
        $this->updateSourcedEventService = $updateSourcedEventService;
    }

    public function __invoke(NewHistoryMessage $message)
    {
        $history = $this->entityManager->getRepository(History::class)->findOneBy(['id'=>$message->getHistoryId()]);
        if (!$history) {
            throw new \Exception('No History Found');
        }

        // Events: If an event is the source event for anything, update that
        foreach ($history->getHistoryHasEvents() as $historyHasEvent) {
            $event = $historyHasEvent->getEvent();
            foreach ($this->entityManager->getRepository(EventHasSourceEvent::class)->findBySourceEvent($event) as $eventHasSourceEvent) {
                $this->updateSourcedEventService->update($eventHasSourceEvent);
            }
        }
    }
}
