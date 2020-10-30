<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\SourceEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }


    public function getBySourceEvent(SourceEvent $sourceEvent) {

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT e ' .
            'FROM App\Entity\Event e ' .
            'JOIN e.eventHasSourceEvents ehse '.
            'WHERE ehse.source_event = :se '.
            'ORDER BY e.startUTC ASC '
        )->setParameter('se', $sourceEvent);

        return $query->execute();

    }

    public function findByHistory(History $history)
    {

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT e ' .
            'FROM App\Entity\Event e ' .
            'JOIN e.histories hhe '.
            'WHERE hhe.history = :h '
        )->setParameter('h', $history);

        return $query->execute();

    }

}
