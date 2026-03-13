<?php

namespace App\Repository;

use App\Entity\Entreprise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entreprise>
 */
class EntrepriseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entreprise::class);
    }

    /**
     * Trouve une entreprise par son nom exact
     */
    public function findByRaisonSociale(string $raison): ?Entreprise
    {
        return $this->createQueryBuilder('e')
            ->where('e.raisonSociale_Entreprise = :raison')
            ->setParameter('raison', $raison)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
