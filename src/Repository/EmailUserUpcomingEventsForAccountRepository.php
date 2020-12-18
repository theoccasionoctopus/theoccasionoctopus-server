<?php

namespace App\Repository;

use App\Entity\Country;
use App\Entity\EmailUserUpcomingEventsForAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class EmailUserUpcomingEventsForAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailUserUpcomingEventsForAccount::class);
    }
}
