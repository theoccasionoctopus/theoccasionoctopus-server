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

    public function findUserCanManage(User $user)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.accountLocal al '.
            'JOIN a.managedByUser uma '.
            'WHERE uma.user = :u AND al.locked = false '.
            'ORDER BY a.title ASC '
        )->setParameter('u', $user);

        return $query->execute();
    }

    public function findFollowing(Account $account)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.followsAccountFollows afa '.
            'WHERE afa.account = :a AND ( afa.follows = true OR afa.followRequested = true )'.
            'ORDER BY a.title ASC '
        )->setParameter('a', $account);

        return $query->execute();
    }

    public function findFollowers(Account $account)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.followsAccount afa '.
            'WHERE afa.followsAccount = :a AND afa.follows = true '.
            'ORDER BY a.title ASC '
        )->setParameter('a', $account);

        return $query->execute();
    }


    public function findFollowersNeedingApproval(Account $account)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.followsAccount afa '.
            'WHERE afa.followsAccount = :a AND afa.followRequested = true AND afa.follows = false '.
            'ORDER BY a.title ASC '
        )->setParameter('a', $account);

        return $query->execute();
    }


    public function findAllLocal()
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.accountLocal al '.
            'ORDER BY a.title ASC '
        );

        return $query->execute();
    }


    public function findAllLocalInDirectoryToFollow(Account $fromAccount)
    {
        $entityManager = $this->getEntityManager();

        // TODO don't include accounts you already follow
        
        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.accountLocal al '.
            'WHERE a.id != :fromAccountId AND al.locked = False AND al.list_in_directory = True ' .
            'ORDER BY a.title ASC '
        )->setParameter('fromAccountId', $fromAccount->getId());

        return $query->execute();
    }


    public function findAllInDirectory()
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.accountLocal al '.
            'WHERE al.list_in_directory = True AND al.locked = False ' .
            'ORDER BY a.title ASC '
        );

        return $query->execute();
    }


    public function findAllRemote()
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.accountRemote ar '.
            'ORDER BY a.title ASC '
        );

        return $query->execute();
    }


    public function findAccountsManagedByUserThatFollowsThisAccount(User $user, Account $account)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a ' .
            'FROM App\Entity\Account a ' .
            'JOIN a.accountLocal al '.
            'JOIN a.managedByUser uma '.
            'JOIN a.followsAccount afa '.
            'WHERE afa.followsAccount = :a AND afa.follows = true AND uma.user = :u AND al.locked = false '.
            ''
        )->setParameter('u', $user)->setParameter('a', $account);

        return $query->execute();
    }
}
