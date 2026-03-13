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
}