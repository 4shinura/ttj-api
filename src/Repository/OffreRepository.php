<?php

namespace App\Repository;

use App\Entity\Offre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offre::class);
    }

    // Exemple : trouver les offres actives
    public function findActive(): array
    {
        return $this->createQueryBuilder('o')
                    ->andWhere('o.statut_Offre = :statut')
                    ->setParameter('statut', 'active')
                    ->getQuery()
                    ->getResult();
    }

    // Trouver toutes les offres avec le statut "en attente"
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('o')
                    ->andWhere('o.statut_Offre = :statut')
                    ->setParameter('statut', $status)
                    ->getQuery()
                    ->getResult();
    }

    // Trouver toutes les offres publiées
    public function findPublished(): array
    {
        return $this->findByStatus('published');
    }

    // Trouver une offre publiée par son id
    public function findPublishedById(int $id): ?Offre
    {
        return $this->createQueryBuilder('o')
                    ->andWhere('o.id = :id')
                    ->andWhere('o.statut_Offre = :statut')
                    ->setParameter('id', $id)
                    ->setParameter('statut', 'published')
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}