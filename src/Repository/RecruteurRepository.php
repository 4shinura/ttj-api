<?php

namespace App\Repository;

use App\Entity\Recruteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recruteur>
 */
class RecruteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recruteur::class);
    }

    public function findByEmail(string $email): ?Recruteur
    {
        return $this->createQueryBuilder('c')
            ->where('c.email_Utilisateur = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retourne tous les recruteurs d'une entreprise donnée
     */
    public function findByEntrepriseId(int $entrepriseId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.entreprise_Recruteur = :id')
            ->setParameter('id', $entrepriseId)
            ->getQuery()
            ->getResult();
    }

}
