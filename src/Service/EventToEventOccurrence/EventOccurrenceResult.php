<?php

namespace App\Service\EventToEventOccurrence;

class EventOccurrenceResult
{

    /** @var \DateTimeInterface  */
    private $startUTC;

    /** @var \DateTimeInterface */
    private $endUTC;

    /**
     * @param $startUTC
     * @param $endUTC
     * @param $startTimezone
     * @param $endTimezone
     */
    public function __construct(\DateTimeInterface $startUTC, \DateTimeInterface $endUTC)
    {
        $this->startUTC = $startUTC;
        $this->endUTC = $endUTC;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getStartUTC(): \DateTimeInterface
    {
        return $this->startUTC;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEndUTC(): \DateTimeInterface
    {
        return $this->endUTC;
    }


}
