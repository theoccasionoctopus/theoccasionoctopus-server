<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\AccountRemote;
use App\Entity\User;
use App\Entity\UserManageAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class AccountRemoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountRemote::class);
    }
}
