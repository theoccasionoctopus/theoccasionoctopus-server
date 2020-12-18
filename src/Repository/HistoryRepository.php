<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\History;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class HistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, History::class);
    }


    public function getLastHistoryForEvent(Event $event):History
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT h ' .
            'FROM App\Entity\History h ' .
            'JOIN h.historyHasEvents hhe '.
            'WHERE hhe.event = :event '.
            'ORDER BY h.created DESC '
        )->setParameter('event', $event)
        ->setMaxResults(1);

        $r = $query->execute();
        return $r ? $r[0] : null;
    }
}
