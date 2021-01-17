<?php

namespace App\Service\Purge;

use App\Entity\EventHasImport;
use App\Entity\EventHasSourceEvent;
use App\Entity\EventHasTag;
use App\Entity\HistoryHasEvent;
use App\Entity\HistoryHasEventHasTag;
use App\Entity\HistoryHasTag;
use App\Entity\Import;
use App\Entity\Source;
use App\Entity\SourceHasTag;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\History;
use App\Entity\Account;
use App\Entity\Event;
use App\Library;
use App\Service\HistoryWorker\HistoryWorker;
use App\Service\HistoryWorker\HistoryWorkerService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Sabre\VObject;
use Psr\Log\LoggerInterface;

class PurgeService
{

    /** @var  EntityManagerInterface */
    protected $entityManager;


    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }


    public function purgeAccount(Account $account)
    {
        $this->entityManager->transactional(function ($em) use ($account) {
            $sqls = [
                // History
                ' DELETE FROM history_has_event WHERE history_id IN (SELECT id FROM history WHERE account_id=:account_id)',
                ' DELETE FROM history_has_event_has_tag WHERE history_id IN (SELECT id FROM history WHERE account_id=:account_id)',
                ' DELETE FROM history_has_tag WHERE history_id IN (SELECT id FROM history WHERE account_id=:account_id)',
                ' DELETE FROM history WHERE account_id=:account_id',
                // Import
                ' DELETE FROM event_has_import WHERE import_id IN (SELECT id FROM import WHERE account_id=:account_id)',
                ' DELETE FROM import WHERE account_id=:account_id',
                // Event
                ' DELETE FROM event_has_source_event WHERE event_id IN (SELECT id FROM event WHERE account_id=:account_id) OR source_event_id IN (SELECT id FROM event WHERE account_id=:account_id)',
                ' DELETE FROM event_has_tag WHERE event_id IN (SELECT id FROM event WHERE account_id=:account_id)',
                ' DELETE FROM event_occurrence WHERE event_id IN (SELECT id FROM event WHERE account_id=:account_id)',
                ' DELETE FROM event WHERE account_id=:account_id',
                // Tags
                ' DELETE FROM tag WHERE account_id=:account_id',
                // Follows
                ' DELETE FROM account_follows_account WHERE account_id = :account_id OR follows_account_id = :account_id',
                // API Keys
                ' DELETE FROM api_access_token WHERE account_id=:account_id',
                // Activity Pub
                ' DELETE FROM tag WHERE account_id=:account_id',
                // User links
                ' DELETE FROM email_user_upcoming_events_for_account WHERE account_id=:account_id',
                ' DELETE FROM user_manage_account WHERE account_id=:account_id',
                // Finally Account
                ' DELETE FROM account_local WHERE account_id=:account_id',
                ' DELETE FROM account_remote WHERE account_id=:account_id',
                ' DELETE FROM account WHERE id=:account_id',
            ];
            foreach ($sqls as $sql) {
                $em->getConnection()->prepare($sql)->execute(['account_id' => $account->getId()]);
            }
        });

        $this->logger->info('Account was purged', ['account_id'=>$account->getId()]);
    }


    public function purgeEvent(Event $event)
    {
        $this->entityManager->transactional(function ($em) use ($event) {
            $sqls = [
                // History
                ' DELETE FROM history_has_event WHERE event_id = :event_id',
                ' DELETE FROM history_has_event_has_tag WHERE event_id = :event_id',
                // Import
                ' DELETE FROM event_has_import WHERE event_id = :event_id',
                // Event
                ' DELETE FROM event_has_source_event WHERE event_id = :event_id',
                ' DELETE FROM event_has_tag WHERE event_id = :event_id',
                ' DELETE FROM event_occurrence WHERE event_id = :event_id',
                ' DELETE FROM event WHERE id = :event_id',
            ];
            foreach ($sqls as $sql) {
                $em->getConnection()->prepare($sql)->execute(['event_id' => $event->getId()]);
            }
            // TODO also need to purge any histories with no content now
        });


        $this->logger->info('Event was purged', ['event_id'=>$event->getId()]);
    }
}
