<?php

namespace App\Repository;

use App\Entity\HistoryHasEventHasSourceEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class HistoryHasEventHasSourceEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryHasEventHasSourceEvent::class);
    }
}
