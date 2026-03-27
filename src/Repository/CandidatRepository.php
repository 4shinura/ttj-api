<?php

namespace App\Repository;

use App\Entity\Candidat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CandidatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidat::class);
    }

    public function findByEmail(string $email): ?Candidat
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(TRIM(c.email_Utilisateur)) = LOWER(TRIM(:email))')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
}