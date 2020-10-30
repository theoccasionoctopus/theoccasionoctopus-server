<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function findUserCanManage(User $user) {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.managedByUser uma '.
            'WHERE uma.user = :u '.
            'ORDER BY a.title ASC '
        )->setParameter('u', $user);

        return $query->execute();
    }

    public function findFollowing(Account $account) {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.followsAccountFollows afa '.
            'WHERE afa.account = :a '.
            'ORDER BY a.title ASC '
        )->setParameter('a', $account);

        return $query->execute();

    }


    public function findAllLocal() {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.accountLocal al '.
            'ORDER BY a.title ASC '
        );

        return $query->execute();
    }


    public function findAllRemote() {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.accountRemote ar '.
            'ORDER BY a.title ASC '
        );

        return $query->execute();
    }


}
