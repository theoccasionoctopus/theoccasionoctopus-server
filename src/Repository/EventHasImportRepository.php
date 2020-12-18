<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventHasImport;
use App\Entity\Import;
use App\Entity\Source;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class EventHasImportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventHasImport::class);
    }
}
