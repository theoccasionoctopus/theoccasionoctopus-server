<?php

namespace App\Service\Import;

use App\Entity\EventHasImport;
use App\Entity\EventHasSourceEvent;
use App\Entity\EventHasTag;
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

class ImportService
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


    public function import(Import $import)
    {
        $this->logger->info('Importing', ['import_id' => $import->getId(), 'account_id'=>$import->getAccount()->getId()]);

        // TODO use new service for requests
        $guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'Prototype Software') )));
        $response = $guzzle->request("GET", $import->getURL(), array());
        if ($response->getStatusCode() != 200) {
            throw new Exception("Got Status " . $response->getStatusCode());
        }

        // TODO this passes a whole string to the reader - if we could pass a stream it would be more efficient
        $vcalendar = VObject\Reader::read($response->getBody()->getContents(), VObject\Reader::OPTION_FORGIVING);

        $this->importVCalender($import, $vcalendar);
    }

    public function importVCalender(Import $import, VObject\Component\VCalendar $vcalendar)
    {
        $historyWorker = $this->historyWorkerService->getHistoryWorker($import->getAccount());

        foreach ($vcalendar->VEVENT as $eventData) {
            $this->importVEVENT($import, $historyWorker, $eventData);
        }

        if ($historyWorker->hasContents()) {
            $this->historyWorkerService->persistHistoryWorker($historyWorker);
        }


        // TODO make event occurrences
    }

    protected function importVEVENT(Import $import, HistoryWorker $historyWorker, $eventData)
    {
        $primary_id_in_data = $eventData->UID;
        $secondary_id_in_data = $eventData->{'RECURRENCE-ID'};
        $eventDataStatus = iconv(mb_detect_encoding($eventData->STATUS), "UTF-8//IGNORE", $eventData->STATUS);
        $eventDataSummary = iconv(mb_detect_encoding($eventData->SUMMARY), "UTF-8//IGNORE", $eventData->SUMMARY);
        $eventDataDescription = iconv(mb_detect_encoding($eventData->DESCRIPTION), "UTF-8//IGNORE", $eventData->DESCRIPTION);
        $eventDataUrl = iconv(mb_detect_encoding($eventData->URL), "UTF-8//IGNORE", $eventData->URL);
        $eventDataRRule = iconv(mb_detect_encoding($eventData->RRULE), "UTF-8//IGNORE", $eventData->RRULE);

        if (!$secondary_id_in_data) {
            // Nulls are not allowed here, it must be an empty string
            $secondary_id_in_data = '';
        }

        $eventHasImport = $this->entityManager->getRepository(EventHasImport::class)->findOneBy(
            ['import'=>$import, 'primaryIdInData'=>$primary_id_in_data, 'secondaryIdInData'=>$secondary_id_in_data]
        );

        $changes = false;
        if ($eventHasImport) {
            $event = $eventHasImport->getEvent();
        } elseif ($eventDataStatus == 'CANCELLED') {
            // It's a new event, but it's cancelled - we don't want to bother importing that at all.
            return;
        } else {
            $event = new Event();
            $event->setId(Library::GUID());
            $event->setAccount($import->getAccount());
            $event->setPrivacy($import->getPrivacy());
            $changes = true;

            $eventHasImport = new EventHasImport();
            $eventHasImport->setEvent($event);
            $eventHasImport->setImport($import);
            $eventHasImport->setPrimaryIdInData($primary_id_in_data);
            $eventHasImport->setSecondaryIdInData($secondary_id_in_data);
            $historyWorker->addEventHasImport($eventHasImport);
        }

        // TODO try and guess from data, not just take the default!
        $event->setCountry($import->getDefaultCountry());
        $event->setTimezone($import->getDefaultTimezone());


        if ($event->setTitle($eventDataSummary)) {
            $changes = true;
        }

        if ($event->setDescription($eventDataDescription)) {
            $changes = true;
        }

        if ($event->setUrl($eventDataUrl)) {
            $changes = true;
        }

        if ($event->setCancelled($eventDataStatus == 'CANCELLED')) {
            $changes = true;
        }

        if ($eventDataRRule) {
            if ($event->setRrule($eventDataRRule)) {
                $changes = true;
            }
            // TODO set RRULE Options
        } else {
            if ($event->setRrule(null)) {
                $changes = true;
            }
            if ($event->setRruleOptions(null)) {
                $changes = true;
            }
        }

        if ($eventData->DTSTART->hasTime()) {
            if ($event->setStartWithObject($eventData->DTSTART->getDateTime())) {
                $changes = true;
            }
            if ($eventData->DTEND) {
                if ($event->setEndWithObject($eventData->DTEND->getDateTime())) {
                    $changes = true;
                }
            } else {
                // TODO should we be looking for duration field here?
                if ($event->setEndWithObject($eventData->DTSTART->getDateTime())) {
                    $changes = true;
                }
            }
        } else {
            $startValue= $eventData->DTSTART->getValue();
            if ($event->setStartWithInts(substr($startValue, 0, 4), substr($startValue, 4, 2), substr($startValue, 6, 2), null, null, null)) {
                $changes = true;
            }
            if ($eventData->DTEND) {
                $endValue = $eventData->DTEND->getDateTime()->sub(new \DateInterval('P1D'));
                if ($event->setEndWithInts($endValue->format('Y'), $endValue->format('n'), $endValue->format('j'), null, null, null)) {
                    $changes = true;
                }
            } else {
                // TODO ??
            }
        }


        if ($changes) {
            $historyWorker->addEvent($event);
        }
    }
}
