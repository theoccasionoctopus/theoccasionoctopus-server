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

class ImportService
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


    public function import(Import $import) {

        $guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'Prototype Software') )));
        $response = $guzzle->request("GET", $import->getURL(), array());
        if ($response->getStatusCode() != 200) {
            throw new Exception("Got Status " . $response->getStatusCode());
        }

        // TODO this passes a whole string to the reader - if we could pass a stream it would be more efficient
        $vcalendar = VObject\Reader::read($response->getBody()->getContents(), VObject\Reader::OPTION_FORGIVING);

        $historyWorker = $this->historyWorkerService->getHistoryWorker($import->getAccount());

        foreach($vcalendar->VEVENT as $eventData) {
            $this->importVEVENT($import, $historyWorker, $eventData);
        }

        if ($historyWorker->hasContents()) {
            $this->historyWorkerService->persistHistoryWorker($historyWorker);
        }


        // TODO make event occurrences

    }

    protected function importVEVENT(Import $import, HistoryWorker $historyWorker, $eventData) {

        $primary_id_in_data = $eventData->UID;
        $secondary_id_in_data = $eventData->{'RECURRENCE-ID'};
        if (!$secondary_id_in_data) {
            // Nulls are not allowed here, it must be an empty string
            $secondary_id_in_data = '';
        }

        $eventHasImport = $this->entityManager->getRepository(EventHasImport::class)->findOneBy(
            ['import'=>$import, 'primaryIdInData'=>$primary_id_in_data, 'secondaryIdInData'=>$secondary_id_in_data]
        );

        $changes = False;
        if ($eventHasImport) {
            $event = $eventHasImport->getEvent();
        } elseif ($eventData->STATUS == 'CANCELLED') {
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


        if ($event->setTitle($eventData->SUMMARY)) {
            $changes = true;
        }

        if ($event->setDescription($eventData->DESCRIPTION)) {
            $changes = true;
        }

        if ($event->setUrl($eventData->URL)) {
            $changes = true;
        }

        if ($event->setCancelled($eventData->STATUS == 'CANCELLED')) {
            $changes = true;
        }

        if($eventData->RRULE) {
            if ($event->setRrule($eventData->RRULE)) {
                $changes = true;
            }
            // TODO set RRULE Options
        } else {
            if ($event->setRrule(Null)) {
                $changes = true;
            }
            if ($event->setRruleOptions(Null)) {
                $changes = true;
            }
        }

        if ($event->setStartWithObject($eventData->DTSTART->getDateTime())) {
            $changes = true;
        }
        if ($event->setEndWithObject($eventData->DTEND->getDateTime())) {
            $changes = true;
        }

        if ($changes) {
            $historyWorker->addEvent($event);
        }

    }

}

