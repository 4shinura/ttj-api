<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function findNotPendingUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.statut_Utilisateur != :status')
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getResult();
    }

    public function findByEmail(string $email): ?Utilisateur
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(TRIM(u.email_Utilisateur)) = LOWER(TRIM(:email))')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(TRIM(u.statut_Utilisateur)) = LOWER(TRIM(:status))')
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
    }
}