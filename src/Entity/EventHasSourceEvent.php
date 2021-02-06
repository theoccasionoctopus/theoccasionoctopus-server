<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventHasSourceEventRepository")
 * @ORM\Table(name="event_has_source_event")
 */
class EventHasSourceEvent
{

    /**
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="eventHasSources")
     */
    private $event;

    /**
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="source_event_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="eventIsSources")
     */
    private $sourceEvent;


    /**
     * @ORM\Column(name="update_all", type="boolean", nullable=false, options={"default" : True})
     */
    private $updateAll = true;


    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return Event
     */
    public function getSourceEvent(): Event
    {
        return $this->sourceEvent;
    }

    /**
     * @param Event $sourceEvent
     */
    public function setSourceEvent(Event $sourceEvent)
    {
        $this->sourceEvent = $sourceEvent;
    }

    /**
     * @return mixed
     */
    public function getUpdateAll()
    {
        return $this->updateAll;
    }

    /**
     * @param mixed $updateAll
     */
    public function setUpdateAll($updateAll)
    {
        $this->updateAll = $updateAll;
    }
}
