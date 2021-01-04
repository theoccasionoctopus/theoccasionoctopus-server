<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Account;
use App\Entity\EventHasSourceEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class EventHasSourceEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventHasSourceEvent::class);
    }

    public function findOneBySourceEventAndDestinationAccount(Event $event, Account $account)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT ehs ' .
            'FROM App\Entity\EventHasSourceEvent ehs ' .
            'JOIN ehs.event e '.
            'WHERE e.account = :a AND ehs.sourceEvent = :e'
        )->setParameter('a', $account)->setParameter('e', $event);

        return $query->getOneOrNullResult();
    }
}
