<?php

namespace App\Service\HistoryWorker;

use App\Entity\EventHasImport;
use App\Entity\EventHasSourceEvent;
use App\Entity\EventHasTag;
use App\Entity\Source;
use App\Entity\SourceHasTag;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\History;
use App\Entity\Account;
use App\Entity\Event;
use App\Library;

class HistoryWorker
{
    protected $history;

    protected $events = array();
    protected $tags = array();
    protected $eventHasTags = array();
    protected $eventHasImports = array();
    protected $eventHasSourceEvents = array();

    /**
     * HistoryWorker constructor.
     */
    public function __construct(Account $account, User $user = null)
    {
        $this->history = new History();
        $this->history->setId(Library::GUID());
        $this->history->setAccount($account);
        $this->history->setCreator($user);
    }

    public function hasContents()
    {
        return count($this->events) ||
            count($this->tags) ||
            count($this->eventHasTags) ||
            count($this->eventHasImports) ||
            count($this->eventHasSourceEvents);
    }

    public function getHistory()
    {
        return $this->history;
    }

    public function addEvent(Event $event)
    {
        $this->events[] = $event;
    }

    public function getEvents()
    {
        return $this->events;
    }


    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }


    /**
     * @return array
     */
    public function getEventHasTags()
    {
        return $this->eventHasTags;
    }

    public function addEventHasTag(EventHasTag $eventHasTag)
    {
        $this->eventHasTags[] = $eventHasTag;
    }

    public function addEventHasSourceEvent(EventHasSourceEvent $eventHasSourceEvent)
    {
        $this->eventHasSourceEvents[] = $eventHasSourceEvent;
    }

    /**
     * @return array
     */
    public function getEventHasSourceEvents(): array
    {
        return $this->eventHasSourceEvents;
    }

    public function addEventHasImport(EventHasImport $eventHasImport)
    {
        $this->eventHasImports[] = $eventHasImport;
    }

    /**
     * @return array
     */
    public function getEventHasImports(): array
    {
        return $this->eventHasImports;
    }
}
