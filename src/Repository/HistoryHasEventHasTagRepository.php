<?php

namespace App\Repository;

use App\Entity\HistoryHasEventHasTag;
use App\Entity\EventHasTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class HistoryHasEventHasTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryHasEventHasTag::class);
    }
}
