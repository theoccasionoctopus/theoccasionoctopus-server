<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 *
 * What happens if 2 imports in 1 account see the same UID in data? We want to leave open option that they may make the same event.
 * So
 *  - event_id is ManyToOne not OneToOne
 *  - Id column is event_id & import_id - not just event_id
 *
 * @ORM\Entity(repositoryClass="App\Repository\EventHasImportRepository")
 * @ORM\Table(name="event_has_import",uniqueConstraints={@ORM\UniqueConstraint(name="event_has_import_import_event_id_in_data_idx", columns={"import_id", "primary_id_in_data", "secondary_id_in_data"})})
 */
class EventHasImport
{

    /**
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
     * This is not OneToOne - what happens if 2 imports in 1 account see the same event?
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="eventHasImport")
     */
    private $event;

    /**
     * @ORM\Id()
     * @var Import
     * @ORM\JoinColumn(name="import_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Import", inversedBy="events")
     *
     */
    private $import;

    /**
     * For iCal, this is UID
     * @ORM\Column(name="primary_id_in_data", type="text", nullable=false)
     */
    private $primaryIdInData;

    /**
     * For iCal, this is RECURRENCE-ID or an empty string if not set.
     *
     * This is NOT nullable=true! This is because if you do, the id_in_data unique constraint will stop working.
     *
     * @ORM\Column(name="secondary_id_in_data", type="text", nullable=false)
     */
    private $secondaryIdInData;

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
    public function getImport()
    {
        return $this->import;
    }

    /**
     * @param mixed $import
     */
    public function setImport($import)
    {
        $this->import = $import;
    }

    /**
     * @return mixed
     */
    public function getPrimaryIdInData()
    {
        return $this->primaryIdInData;
    }

    /**
     * @param mixed $primaryIdInData
     */
    public function setPrimaryIdInData($primaryIdInData)
    {
        $this->primaryIdInData = $primaryIdInData;
    }

    /**
     * @return mixed
     */
    public function getSecondaryIdInData()
    {
        return $this->secondaryIdInData;
    }

    /**
     * @param mixed $secondaryIdInData
     */
    public function setSecondaryIdInData($secondaryIdInData)
    {
        $this->secondaryIdInData = $secondaryIdInData;
    }




}
