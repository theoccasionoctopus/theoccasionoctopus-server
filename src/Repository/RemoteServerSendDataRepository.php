<?php

namespace App\Repository;

use App\Entity\AccountLocal;
use App\Entity\RemoteServerSendData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class RemoteServerSendDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RemoteServerSendData::class);
    }

    public function getLatestForAccountLocal(AccountLocal $accountLocal)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT rssd ' .
            'FROM App\Entity\RemoteServerSendData rssd ' .
            'WHERE rssd.fromAccount = :a '.
            'ORDER BY rssd.created DESC '
        )->setParameter('a', $accountLocal->getAccount())->setMaxResults(50);

        return $query->execute();
    }
}
