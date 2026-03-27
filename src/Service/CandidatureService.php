<?php

namespace App\Service;

use App\Entity\Candidature;
use App\Repository\CandidatureRepository;
use Doctrine\ORM\EntityManagerInterface;

class CandidatureService
{
    public function __construct(
        private CandidatureRepository $repository,
        private EntityManagerInterface $em
    ) {}

    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    public function getByCandidat(int $id): array
    {
        return $this->repository->findByCandidat($id);
    }

    public function getByOffre(int $id): array
    {
        return $this->repository->findByOffre($id);
    }

    public function getById(int $id): ?Candidature
    {
        return $this->repository->find($id);
    }

    public function create(Candidature $candidature): Candidature
    {
        $this->em->persist($candidature);
        $this->em->flush();

        return $candidature;
    }

    public function update(Candidature $candidature): Candidature
    {
        $this->em->flush();

        return $candidature;
    }

    public function delete(Candidature $candidature): void
    {
        $this->em->remove($candidature);
        $this->em->flush();
    }
}