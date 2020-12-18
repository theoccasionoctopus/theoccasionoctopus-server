<?php

namespace App\Repository;

use App\Entity\CountryHasTimeZone;
use App\Entity\EventHasSourceEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class CountryHasTimeZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CountryHasTimeZone::class);
    }
}
