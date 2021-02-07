<?php

namespace App\Repository;

use App\Entity\AccountLocal;
use App\Entity\InboxSubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class InboxSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InboxSubmission::class);
    }

    public function getLatestForAccountLocal(AccountLocal $accountLocal)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT iso ' .
            'FROM App\Entity\InboxSubmission iso ' .
            'WHERE iso.account = :a '.
            'ORDER BY iso.created DESC '
        )->setParameter('a', $accountLocal->getAccount())->setMaxResults(50);

        return $query->execute();
    }
}
