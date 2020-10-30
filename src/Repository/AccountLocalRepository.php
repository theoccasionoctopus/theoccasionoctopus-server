<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\User;
use App\Entity\UserManageAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class AccountLocalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountLocal::class);
    }


}
