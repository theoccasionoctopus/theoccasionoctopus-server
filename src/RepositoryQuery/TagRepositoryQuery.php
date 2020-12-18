<?php

namespace App\RepositoryQuery;

use App\Entity\Account;
use App\Entity\Event;
use App\Entity\Tag;
use App\Repository\EventRepository;

class TagRepositoryQuery
{
    protected $doctrine;

    /** @var  Account */
    protected $account;


    protected $max_privacy_allowed = 10000;

    /**
     * EventRepositoryQuery constructor.
     * @param $doctrine
     * @param Account $account
     */
    public function __construct($doctrine, Account $account)
    {
        $this->doctrine = $doctrine;
        $this->account = $account;
    }

    public function setPublicOnly()
    {
        $this->max_privacy_allowed = 0;
    }


    public function getTags()
    {
        $qb = $this->doctrine->getRepository(Tag::class)->createQueryBuilder('t');

        $qb->andWhere('t.account = :account ANd t.privacy <= :privacy')
            ->setParameter('account', $this->account)
            ->setParameter('privacy', $this->max_privacy_allowed)
        ;


        $qb->orderBy('t.title', 'ASC');

        return $qb->getQuery()->execute();
    }
}
