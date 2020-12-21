<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Helper\TraitExtraFields;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\Table(name="event")
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @Assert\NotNull
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="events")
     */
    private $account;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="json", nullable=true, options={"jsonb"=true})
     */
    private $extra_fields;

    use TraitExtraFields;

    /**
     * @ORM\Column(name="start_year", type="smallint", nullable=false)
     */
    private $startYear;

    /**
     * @ORM\Column(name="start_month", type="smallint", nullable=false)
     */
    private $startMonth;

    /**
     * @ORM\Column(name="start_day", type="smallint", nullable=false)
     */
    private $startDay;

    /**
     * @ORM\Column(name="start_hour", type="smallint", nullable=false)
     */
    private $startHour;

    /**
     * @ORM\Column(name="start_minute", type="smallint", nullable=false)
     */
    private $startMinute;

    /**
     * @ORM\Column(name="start_second", type="smallint", nullable=false)
     */
    private $startSecond;

    /**
     * @ORM\Column(name="end_year", type="smallint", nullable=false)
     */
    private $endYear;

    /**
     * @ORM\Column(name="end_month", type="smallint", nullable=false)
     */
    private $endMonth;

    /**
     * @ORM\Column(name="end_day", type="smallint", nullable=false)
     */
    private $endDay;

    /**
     * @ORM\Column(name="end_hour", type="smallint", nullable=false)
     */
    private $endHour;

    /**
     * @ORM\Column(name="end_minute", type="smallint", nullable=false)
     */
    private $endMinute;

    /**
     * @ORM\Column(name="end_second", type="smallint", nullable=false)
     */
    private $endSecond;

    /**
     * @ORM\Column(name="cached_start_epoch", type="integer", nullable=false)
     */
    private $cachedStartEpoch;

    /**
     * @ORM\Column(name="cached_end_epoch", type="integer", nullable=false)
     */
    private $cachedEndEpoch;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $cancelled = false;

    /**
     * @var Country
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="App\Entity\Country", inversedBy="events")
     *
     */
    private $country;

    /**
     * @var TimeZone
     * @ORM\JoinColumn(name="timezone_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="App\Entity\TimeZone", inversedBy="events")
     *
     */
    private $timezone;


    /**
     *
     *
     * @ORM\Column(name="privacy", type="smallint", unique=false, nullable=false, options={"default" : 10000})
     */
    private $privacy;

    /**
     * @ORM\Column(name="url", type="text", nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(name="url_tickets", type="text", nullable=true)
     */
    private $url_tickets;

    /**
     * @ORM\Column(name="rrule", type="text", nullable=true)
     */
    private $rrule;

    /**
     * @ORM\Column(type="json", nullable=true, options={"jsonb"=true})
     */
    private $rrule_options;

    /**
     *
     * @ORM\Column(name="activitypub_id", type="string", length=2000,  nullable=true, unique=true)
     */
    private $activitypubId;

    /**
     * @ORM\OneToMany(targetEntity="EventHasTag", mappedBy="event")
     */
    private $eventHasTags;

    /**
     * @ORM\OneToMany(targetEntity="HistoryHasEvent", mappedBy="event")
     */
    private $histories;

    /**
     * @ORM\OneToMany(targetEntity="EventHasSourceEvent", mappedBy="event")
     */
    private $eventHasSources;

    /**
     * @ORM\OneToMany(targetEntity="EventHasImport", mappedBy="event")
     */
    private $eventHasImport;


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

    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     * @return bool Was there any changes?
     * @param mixed $title
     */
    public function setTitle($title): bool
    {
        if ($this->title != $title) {
            $this->title = $title;
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return bool Was there any changes?
     */
    public function setDescription($description): bool
    {
        if ($this->description != $description) {
            $this->description = $description;
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getStart($timezone = null)
    {
        $out = new \DateTime('', new \DateTimeZone($this->timezone->getCode()));
        $out->setDate($this->startYear, $this->startMonth, $this->startDay);
        $out->setTime($this->startHour, $this->startMinute, $this->startSecond);
        if ($timezone && $timezone != $this->timezone->getCode()) {
            $out->setTimezone(new \DateTimeZone($timezone));
        }
        return $out;
    }

    public function getStartAtTimeZone()
    {
        $out = new \DateTime('', new \DateTimeZone($this->timezone->getCode()));
        $out->setDate($this->startYear, $this->startMonth, $this->startDay);
        $out->setTime($this->startHour, $this->startMinute, $this->startSecond);
        return $out;
    }

    /**
     * @return bool Was there any changes?
     */
    public function setStartWithObject(\DateTimeInterface $start): bool
    {
        $d = clone $start;
        if ($start->getTimezone()->getName() != $this->getTimezone()->getCode()) {
            $d->setTimezone(new \DateTimeZone($this->timezone->getCode()));
        }
        return $this->setStartWithInts(
            $d->format('Y'),
            $d->format('n'),
            $d->format('j'),
            $d->format('G'),
            $d->format('i'),
            $d->format('s'),
        );
    }
    
    /**
     * @return bool Was there any changes?
     */
    public function setStartWithInts(int $year, int $month, int $day, int $hour, int $minute, int $second): bool
    {
        if ($this->startYear != $year || $this->startMonth != $month || $this->startDay != $day || $this->startHour != $hour || $this->startMinute != $minute || $this->startSecond != $second) {
            $this->startYear = $year;
            $this->startMonth = $month;
            $this->startDay = $day;
            $this->startHour = $hour;
            $this->startMinute = $minute;
            $this->startSecond = $second;
            $this->updateStartEndCache();
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getEnd($timezone = null)
    {
        $out = new \DateTime('', new \DateTimeZone($this->timezone->getCode()));
        $out->setDate($this->endYear, $this->endMonth, $this->endDay);
        $out->setTime($this->endHour, $this->endMinute, $this->endSecond);
        if ($timezone && $timezone != $this->timezone->getCode()) {
            $out->setTimezone(new \DateTimeZone($timezone));
        }
        return $out;
    }

    public function getEndAtTimeZone()
    {
        $out = new \DateTime('', new \DateTimeZone($this->timezone->getCode()));
        $out->setDate($this->endYear, $this->endMonth, $this->endDay);
        $out->setTime($this->endHour, $this->endMinute, $this->endSecond);
        return $out;
    }
    
    /**
     * @return bool Was there any changes?
     */
    public function setEndWithObject(\DateTimeInterface $end): bool
    {
        $d = clone $end;
        if ($end->getTimezone()->getName() != $this->getTimezone()->getCode()) {
            $d->setTimezone(new \DateTimeZone($this->timezone->getCode()));
        }
        return $this->setEndWithInts(
            $d->format('Y'),
            $d->format('n'),
            $d->format('j'),
            $d->format('G'),
            $d->format('i'),
            $d->format('s'),
        );
    }
    
    /**
     * @return bool Was there any changes?
     */
    public function setEndWithInts(int $year, int $month, int $day, int $hour, int $minute, int $second): bool
    {
        if ($this->endYear != $year || $this->endMonth != $month || $this->endDay != $day || $this->endHour != $hour || $this->endMinute != $minute || $this->endSecond != $second) {
            $this->endYear = $year;
            $this->endMonth = $month;
            $this->endDay = $day;
            $this->endHour = $hour;
            $this->endMinute = $minute;
            $this->endSecond = $second;
            $this->updateStartEndCache();
            return true;
        }
        return false;
    }


    
    public function updateStartEndCache()
    {
        if ($this->timezone) {
            $start = new \DateTime('', new \DateTimeZone($this->timezone->getCode()));
            $start->setDate($this->startYear, $this->startMonth, $this->startDay);
            $start->setTime($this->startHour, $this->startMinute, $this->startSecond);
            $this->cachedStartEpoch = $start->getTimestamp();

            $end = new \DateTime('', new \DateTimeZone($this->timezone->getCode()));
            $end->setDate($this->endYear, $this->endMonth, $this->endDay);
            $end->setTime($this->endHour, $this->endMinute, $this->endSecond);
            $this->cachedEndEpoch = $end->getTimestamp();
        }
    }


    /**
     * @return mixed
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param mixed $deleted
     */
    public function setDeleted($deleted): bool
    {
        if ($this->deleted != $deleted) {
            $this->deleted = $deleted;
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getCancelled()
    {
        return $this->cancelled;
    }

    /**
     * @param mixed $cancelled
     * @return bool Was there any changes?
     */
    public function setCancelled($cancelled): bool
    {
        if ($this->cancelled != $cancelled) {
            $this->cancelled = $cancelled;
            return true;
        }
        return false;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param Country $country
     */
    public function setCountry(Country $country): bool
    {
        if (!$this->country || $this->country->getId() != $country->getId()) {
            $this->country = $country;
            return true;
        }
        return false;


        $this->country = $country;
    }

    /**
     * @return TimeZone
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param TimeZone $timezone
     */
    public function setTimezone($timezone): bool
    {
        if (!$this->timezone || $this->timezone->getCode() != $timezone->getCode()) {
            $this->timezone = $timezone;
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }

    /**
     * @param mixed $privacy
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return bool Was there any changes?
     */
    public function setUrl($url): bool
    {
        if ($this->url != $url) {
            $this->url = $url;
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getUrlTickets()
    {
        return $this->url_tickets;
    }

    /**
     * @param mixed $url_tickets
     */
    public function setUrlTickets($url_tickets):bool
    {
        if ($this->url_tickets != $url_tickets) {
            $this->url_tickets = $url_tickets;
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getRrule()
    {
        return $this->rrule;
    }

    /**
     * @return bool Was there any changes?
     * @param mixed $rrule
     */
    public function setRrule($rrule)
    {
        if ($this->rrule != $rrule) {
            $this->rrule = $rrule;
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getRruleOptions()
    {
        return $this->rrule_options;
    }

    /**
     * @return bool Was there any changes?
     * @param mixed $rrule_options
     */
    public function setRruleOptions($rrule_options)
    {
        if ($this->rrule_options != $rrule_options) {
            $this->rrule_options = $rrule_options;
            return true;
        }
        return false;
    }


    public function hasReoccurence(): bool
    {
        return (bool)$this->rrule;
    }

    public function copyFromEvent(Event $sourceEvent)
    {
        $r = false;
        if ($this->setTitle($sourceEvent->getTitle())) {
            $r = true;
        }
        if ($this->setDescription($sourceEvent->getDescription())) {
            $r = true;
        }
        if ($this->setUrl($sourceEvent->getUrl())) {
            $r = true;
        }
        if ($this->setUrlTickets($sourceEvent->getUrlTickets())) {
            $r = true;
        }
        if ($this->setExtraFields($sourceEvent->getExtraFields())) {
            $r = true;
        }
        if ($this->setCountry($sourceEvent->getCountry())) {
            $r = true;
        }
        if ($this->setTimezone($sourceEvent->getTimezone())) {
            $r = true;
        }
        if ($this->setStartWithObject($sourceEvent->getStart())) {
            $r = true;
        }
        if ($this->setEndWithObject($sourceEvent->getEnd())) {
            $r = true;
        }
        if ($this->setCancelled($sourceEvent->getCancelled())) {
            $r = true;
        }
        if ($this->setDeleted($sourceEvent->getDeleted())) {
            $r = true;
        }
        return $r;
    }

    // These values are exported via the API so should be changed with caution
    const EDITABLE_FIELDS_MODE_ALL = 'all';
    const EDITABLE_FIELDS_MODE_IMPORTED = 'imported';
    const EDITABLE_FIELDS_MODE_SOURCED = 'sourced';

    public function getEditableFieldsMode():string
    {

        // Is this an actively imported event?
        if ($this->eventHasImport) {
            foreach ($this->eventHasImport as $eventHasImport) {
                if ($eventHasImport->getImport()->getEnabled()) {
                    return self::EDITABLE_FIELDS_MODE_IMPORTED;
                }
            }
        }


        // Is this an event sourced?
        if ($this->eventHasSources) {
            foreach ($this->eventHasSources as $eventHasSource) {
                if (true) { # TODO This should be some check of whether updates from the source are still wanted
                    return self::EDITABLE_FIELDS_MODE_SOURCED;
                }
            }
        }


        // Ok, can edit everything
        return self::EDITABLE_FIELDS_MODE_ALL;
    }

    public function getEditableFieldsList():array
    {
        $mode = $this->getEditableFieldsMode();
        if ($mode == self::EDITABLE_FIELDS_MODE_SOURCED) {
            return [
                'privacy',
                'tags',
            ];
        } elseif ($mode == self::EDITABLE_FIELDS_MODE_IMPORTED) {
            return [
                'privacy',
                'country',
                'timezone',
                'extra_fields',
                'tags',
            ];
        } else {
            return [
              'title',
              'description',
              'url',
              'url_tickets',
              'start_end',
              'rrule',
              'privacy',
              'country',
              'timezone',
              'extra_fields',
              'deleted',
              'cancelled',
              'tags',
            ];
        }
    }

    /**
     * @return mixed
     */
    public function getActivitypubId()
    {
        return $this->activitypubId;
    }

    /**
     * @param mixed $activitypubId
     */
    public function setActivitypubId($activitypubId)
    {
        $this->activitypubId = $activitypubId;
    }
}
