<?php

namespace App\Repository;

use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }

    public function findByCandidat(int $candidatId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.candidat = :id')
            ->setParameter('id', $candidatId)
            ->getQuery()
            ->getResult();
    }

    public function findByOffre(int $offreId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.offre = :id')
            ->setParameter('id', $offreId)
            ->getQuery()
            ->getResult();
    }
}