<?php

namespace App\Entity;

use App\Entity\Helper\InterfaceStartEnd;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Library;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventOccurrenceRepository")
 * @ORM\Table(
 *     name="event_occurrence",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="event_occurrence_event_start_idx", columns={"event_id", "start_epoch"}),
 *         @ORM\UniqueConstraint(name="event_occurrence_event_slug_idx", columns={"event_id","slug"})
 *     }
 * )
 */
class EventOccurrence implements InterfaceStartEnd
{

    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @Assert\NotNull
     * @ORM\Column(type="guid")
     */
    private $slug;

    /**
     * @Assert\NotNull
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="occurrences")
     */
    private $event;

    /**
     * This column is not marked Cached even thought it is calculated from data on Event table and set here.
     * It is given it's own ID as it might be referenced directly by other objects.
     * @ORM\Column(name="start_epoch", type="integer", nullable=false)
     */
    private $startEpoch;

    /**
     * This column is not marked Cached even thought it is calculated from data on Event table and set here.
     * It is given it's own ID as it might be referenced directly by other objects.
     * @ORM\Column(name="end_epoch", type="integer", nullable=false)
     */
    private $endEpoch;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug)
    {
        $this->slug = $slug;
    }

    public function setNewIdAndSlug()
    {
        // TODO be passed doctrine manager; check Id & slug do not already exist
        $this->id = Library::GUID();
        $this->slug = $this->id;
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


    public function isAllDay(): bool
    {
        return $this->event->isAllDay();
    }

    /**
     * @return \DateTime
     */
    public function getStart($timezone = null): \DateTime
    {
        $timezone = ($timezone ? new \DateTimeZone($timezone) : new \DateTimeZone($this->getEvent()->getTimezone()->getCode()));
        $out = new \DateTime('', $timezone);
        $out->setTimestamp($this->startEpoch);
        return $out;
    }
    
    /**
     * @return \DateTime
     */
    public function getStartAtTimeZone(): \DateTime
    {
        $out = new \DateTime('', new \DateTimeZone($this->getEvent()->getTimezone()->getCode()));
        $out->setTimestamp($this->startEpoch);
        return $out;
    }


    /**
     * @return \DateTime
     */
    public function getEnd($timezone = null): \DateTime
    {
        $timezone = ($timezone ? new \DateTimeZone($timezone) : new \DateTimeZone($this->getEvent()->getTimezone()->getCode()));
        $out = new \DateTime('', $timezone);
        $out->setTimestamp($this->endEpoch);
        return $out;
    }
    
    /**
     * @return \DateTime
     */
    public function getEndAtTimeZone(): \DateTime
    {
        $out = new \DateTime('', new \DateTimeZone($this->getEvent()->getTimezone()->getCode()));
        $out->setTimestamp($this->endEpoch);
        return $out;
    }

    /**
     * @param mixed $startEpoch
     */
    public function setStartWithObject(\DateTimeInterface $startEpoch)
    {
        $this->startEpoch = $startEpoch->getTimestamp();
    }

    /**
     * @param mixed $endEpoch
     */
    public function setEndWithObject(\DateTimeInterface $endEpoch)
    {
        $this->endEpoch = $endEpoch->getTimestamp();
    }
}
