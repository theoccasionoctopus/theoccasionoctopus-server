<?php

namespace App\RepositoryQuery;

use App\Constants;
use App\Entity\Account;
use App\Entity\Event;
use App\Entity\EventOccurrence;
use App\Entity\Tag;
use App\Repository\EventRepository;

class EventRepositoryQuery
{
    protected $doctrine;

    /** @var  Account */
    protected $accountEvents;


    /** @var  Account */
    protected $accountDiscoverEvents;



    /** @var  Tag */
    protected $tag;


    /** @var  \DateTime In UTC */
    protected $from;

    /** @var  \DateTime */
    protected $to;


    protected $url;

    protected $showDeleted = true;

    protected $showCancelled = true;

    protected $max_privacy_allowed = Constants::PRIVACY_LEVEL_PRIVATE;

    const START_END_MODE_ALL_EVENTS = 0;
    const START_END_MODE_STARTING_EVENTS_ONLY = 1;
    protected $start_end_mode = self::START_END_MODE_ALL_EVENTS;

    /** @var null Int */
    protected $limit = null;

    /**
     * EventRepositoryQuery constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function setAccountEvents(Account $account)
    {
        $this->accountEvents = $account;
    }

    public function setAccountDiscoverEvents(Account $accountDiscoverEvents)
    {
        $this->accountDiscoverEvents = $accountDiscoverEvents;
    }

    /**
     * @TODO Name setPrivacyLevelPublic
     */
    public function setPublicOnly()
    {
        $this->max_privacy_allowed = Constants::PRIVACY_LEVEL_PUBLIC;
    }

    public function setPrivacyLevelOnlyFollowers()
    {
        $this->max_privacy_allowed = Constants::PRIVACY_LEVEL_ONLY_FOLLOWERS;
    }

    public function setFrom(\DateTime $from)
    {
        $this->from = $from;
    }

    public function setFromNow()
    {
        $this->from = new \DateTime('', new \DateTimeZone('UTC'));
    }

    public function setTo(\DateTime $to)
    {
        $this->to = $to;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param Tag $tag
     */
    public function setTag(Tag $tag)
    {
        $this->tag = $tag;
    }

    /**
     * @param bool $showDeleted
     */
    public function setShowDeleted($showDeleted)
    {
        $this->showDeleted = $showDeleted;
    }

    /**
     * @param bool $showCancelled
     */
    public function setShowCancelled($showCancelled)
    {
        $this->showCancelled = $showCancelled;
    }



    /**
     * @param null $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @param int $start_end_mode
     */
    public function setStartEndMode(int $start_end_mode)
    {
        $this->start_end_mode = $start_end_mode;
    }

    public function getEvents()
    {
        $qb = $this->doctrine->getRepository(Event::class)->createQueryBuilder('e');

        $qb->andWhere('e.privacy <= :privacy')
            ->setParameter('privacy', $this->max_privacy_allowed)
        ;

        if ($this->accountEvents) {
            $qb->andWhere('e.account = :account')
                ->setParameter('account', $this->accountEvents);
        }

        if ($this->accountDiscoverEvents) {
            $qb->join('e.account', 'a');
            $qb->join('a.followsAccountFollows', 'afa', 'WITH', 'afa.account  = :account AND afa.follows = true');
            $qb->setParameter('account', $this->accountDiscoverEvents);
            // TODO Can we have (... or OR afa.followRequested = true)?
            // To do this, must check
            // * privacy is Public if only followRequested = true
            // * privacy is Public or Only Followers if afa.follows = true
        }

        if ($this->from) {
            if ($this->start_end_mode == self::START_END_MODE_ALL_EVENTS) {
                $qb->andWhere('e.cachedEndEpoch >= :from')->setParameter('from', $this->from->getTimestamp());
            } else {
                $qb->andWhere('e.cachedStartEpoch >= :from')->setParameter('from', $this->from->getTimestamp());
            }
        }

        if ($this->to) {
            $qb->andWhere('e.cachedStartEpoch <= :to')->setParameter('to', $this->to->getTimestamp());
        }

        if ($this->url) {
            $qb->andWhere('e.url = :url')->setParameter('url', $this->url);
        }

        if (!$this->showCancelled) {
            $qb->andWhere('e.cancelled = :cancelled')->setParameter('cancelled', false);
        }

        if (!$this->showDeleted) {
            $qb->andWhere('e.deleted = :deleted')->setParameter('deleted', false);
        }

        if ($this->tag) {
            $qb->join('e.eventHasTags', 'eht', 'WITH', 'eht.tag = :tag');
            $qb->setParameter('tag', $this->tag);
        }

        if ($this->limit) {
            $qb->setMaxResults($this->limit);
        }

        $qb->addOrderBy('e.cachedStartEpoch', 'ASC');
        // If multiple events on at same time, make sure we always have a consistent ordering anyway.
        $qb->addOrderBy('e.title', 'ASC');


        return $qb->getQuery()->execute();
    }


    public function getEventOccurrences()
    {
        $qb = $this->doctrine->getRepository(EventOccurrence::class)->createQueryBuilder('eo');
        $qb->join('eo.event', 'e');

        $qb->andWhere('e.privacy <= :privacy')
            ->setParameter('privacy', $this->max_privacy_allowed)
        ;

        if ($this->accountEvents) {
            $qb->andWhere('e.account = :account')
                ->setParameter('account', $this->accountEvents);
        }

        if ($this->accountDiscoverEvents) {
            $qb->join('e.account', 'a');
            $qb->join('a.followsAccountFollows', 'afa', 'WITH', 'afa.account  = :account AND (afa.follows = true OR afa.followRequested = true)');
            $qb->setParameter('account', $this->accountDiscoverEvents);
        }

        if ($this->from) {
            if ($this->start_end_mode == self::START_END_MODE_ALL_EVENTS) {
                $qb->andWhere('eo.endEpoch >= :from')->setParameter('from', $this->from->getTimestamp());
            } else {
                $qb->andWhere('eo.startEpoch >= :from')->setParameter('from', $this->from->getTimestamp());
            }
        }

        if ($this->to) {
            $qb->andWhere('eo.startEpoch <= :to')->setParameter('to', $this->to->getTimestamp());
        }

        if ($this->url) {
            $qb->andWhere('e.url = :url')->setParameter('url', $this->url);
        }

        if (!$this->showCancelled) {
            $qb->andWhere('e.cancelled = :cancelled')->setParameter('cancelled', false);
        }

        if (!$this->showDeleted) {
            $qb->andWhere('e.deleted = :deleted')->setParameter('deleted', false);
        }

        if ($this->tag) {
            $qb->join('e.eventHasTags', 'eht', 'WITH', 'eht.tag = :tag');
            $qb->setParameter('tag', $this->tag);
        }

        if ($this->limit) {
            $qb->setMaxResults($this->limit);
        }

        $qb->addOrderBy('eo.startEpoch', 'ASC');
        // If multiple events on at same time, make sure we always have a consistent ordering anyway.
        $qb->addOrderBy('e.title', 'ASC');

        return $qb->getQuery()->execute();
    }
}
