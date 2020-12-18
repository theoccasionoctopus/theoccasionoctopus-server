<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Source;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }


    public function findByEvent(Event $event)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT t ' .
            'FROM App\Entity\Tag t ' .
            'JOiN t.events et ' .
            'WHERE et.event = :event AND et.enabled = :enabled '.
            'ORDER BY t.title ASC '
        )->setParameter('event', $event)
            ->setParameter('enabled', true)
        ;

        return $query->execute();
    }

    public function findPublicByEvent(Event $event)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT t ' .
            'FROM App\Entity\Tag t ' .
            'JOiN t.events et ' .
            'WHERE et.event = :event AND et.enabled = :enabled AND t.privacy = 0'.
            'ORDER BY t.title ASC '
        )->setParameter('event', $event)
            ->setParameter('enabled', true)
        ;

        return $query->execute();
    }
}
