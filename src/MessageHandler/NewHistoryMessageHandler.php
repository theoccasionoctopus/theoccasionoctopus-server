<?php

namespace App\MessageHandler;

use App\Constants;
use App\Entity\Account;
use App\Entity\EventHasSourceEvent;
use App\Entity\History;
use App\Entity\Event;
use App\Message\NewHistoryMessage;
use App\Service\AccountRemote\AccountRemoteService;
use App\Service\ActivityPubData\ActivityPubDataService;
use App\Service\UpdateSourcedEvent\UpdateSourcedEventService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

class NewHistoryMessageHandler implements MessageHandlerInterface
{


    /** @var UpdateSourcedEventService */
    protected $updateSourcedEventService;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /** @var AccountRemoteService */
    protected $accountRemoteService;

    /** @var ActivityPubDataService */
    protected $activityPubDataService;

    public function __construct(EntityManagerInterface $entityManager, UpdateSourcedEventService $updateSourcedEventService, AccountRemoteService $accountRemoteService, ActivityPubDataService $activityPubDataService)
    {
        $this->entityManager = $entityManager;
        $this->updateSourcedEventService = $updateSourcedEventService;
        $this->accountRemoteService = $accountRemoteService;
        $this->activityPubDataService = $activityPubDataService;
    }

    public function __invoke(NewHistoryMessage $message)
    {
        /** @var History $history */
        $history = $this->entityManager->getRepository(History::class)->findOneBy(['id'=>$message->getHistoryId()]);
        if (!$history) {
            throw new \Exception('No History Found');
        }

        $this->updateAnyEventsThatHaveTheseAsSources($history);
        $this->sendEventsToRemoteFollowers($history);
    }

    protected function updateAnyEventsThatHaveTheseAsSources(History $history)
    {
        foreach ($history->getHistoryHasEvents() as $historyHasEvent) {
            /** @var Event $event */
            $event = $historyHasEvent->getEvent();
            foreach ($this->entityManager->getRepository(EventHasSourceEvent::class)->findBySourceEvent($event) as $eventHasSourceEvent) {
                $this->updateSourcedEventService->update($eventHasSourceEvent);
            }
        }
    }

    protected function sendEventsToRemoteFollowers(History $history)
    {
        $remoteFollowers = $this->entityManager->getRepository(Account::class)->findRemoteFollowers($history->getAccount());
        if ($remoteFollowers) {
            foreach ($history->getHistoryHasEvents() as $historyHasEvent) {
                /** @var Event $event */
                $event = $historyHasEvent->getEvent();
                // TODO need to handle only followers events to
                if ($event->getPrivacy() == Constants::PRIVACY_LEVEL_PUBLIC) {
                    foreach ($remoteFollowers as $remoteFollower) {
                        // TODO should work out here if we are sending a create or an update
                        $data = $this->activityPubDataService->generateCreateActivityForEvent($event);
                        $this->accountRemoteService->postToInbox(
                            $history->getAccount()->getAccountLocal(),
                            $remoteFollower->getAccountRemote(),
                            $data
                        );
                    }
                }
            }
        }
    }
}
