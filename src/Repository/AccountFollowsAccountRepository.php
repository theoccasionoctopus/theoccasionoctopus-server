<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\AccountFollowsAccount;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class AccountFollowsAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountFollowsAccount::class);
    }


}
