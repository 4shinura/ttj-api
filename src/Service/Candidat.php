<?php

namespace App\Service;

use App\Entity\Candidat;
use App\Repository\CandidatRepository;
use Doctrine\ORM\EntityManagerInterface;

class CandidatService
{
    public function __construct(
        private CandidatRepository $repository,
        private EntityManagerInterface $em
    ) {}

    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    public function getById(int $id): ?Candidat
    {
        return $this->repository->find($id);
    }

    public function create(array $data): Candidat
    {
        $candidat = new Candidat();

        $candidat->setNomUtilisateur($data['nom'])
            ->setPrenomUtilisateur($data['prenom'])
            ->setEmailUtilisateur($data['email'])
            ->setMdpUtilisateur($data['mdp'])
            ->setStatutUtilisateur($data['statut']);

        $this->em->persist($candidat);
        $this->em->flush();

        return $candidat;
    }

    public function delete(Candidat $candidat): void
    {
        $this->em->remove($candidat);
        $this->em->flush();
    }
}