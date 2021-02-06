<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HistoryHasEventHasSourceEventRepository")
 * @ORM\Table(name="history_has_event_has_source_event")
 */
class HistoryHasEventHasSourceEvent
{



    /**
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="history_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\History", inversedBy="histories")
     */
    private $history;

    /**
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="histories")
     */
    private $event;

    /**
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="source_event_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="histories")
     */
    private $sourceEvent;



    /**
     * @return mixed
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param mixed $history
     */
    public function setHistory($history)
    {
        $this->history = $history;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return mixed
     */
    public function getSourceEvent()
    {
        return $this->sourceEvent;
    }

    /**
     * @param mixed $sourceEvent
     */
    public function setSourceEvent($sourceEvent)
    {
        $this->sourceEvent = $sourceEvent;
    }
}
