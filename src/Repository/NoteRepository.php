<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    public function getForOutboxOfAccount(Account $account)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT n ' .
            'FROM App\Entity\Note n ' .
            'WHERE n.account = :a '.
            'ORDER BY n.created DESC '
        )->setParameter('a', $account)->setMaxResults(20);

        return $query->execute();
    }
}
