<?php

namespace App\RepositoryQuery;

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

    protected $max_privacy_allowed = 10000;

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

    public function setPublicOnly()
    {
        $this->max_privacy_allowed = 0;
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
        }

        if ($this->from) {
            $qb->andWhere('e.cachedEndEpoch >= :from')->setParameter('from', $this->from->getTimestamp());
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

        $qb->orderBy('e.cachedStartEpoch', 'ASC');

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
            $qb->join('a.followsAccountFollows', 'afa', 'WITH', 'afa.account  = :account AND afa.follows = true');
            $qb->setParameter('account', $this->accountDiscoverEvents);
        }

        if ($this->from) {
            $qb->andWhere('e.cachedEndEpoch >= :from')->setParameter('from', $this->from->getTimestamp());
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

        $qb->orderBy('eo.startEpoch', 'ASC');

        return $qb->getQuery()->execute();
    }
}
