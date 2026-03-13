<?php
namespace App\Service;

use App\Entity\Recruteur;
use App\Repository\RecruteurRepository;
use Doctrine\ORM\EntityManagerInterface;

class RecruteurService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RecruteurRepository $repository
    ) {}

    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    public function getById(int $id): ?Recruteur
    {
        return $this->repository->find($id);
    }

    public function create(Recruteur $recruteur): Recruteur
    {
        $this->em->persist($recruteur);
        $this->em->flush();
        return $recruteur;
    }

    public function update(): void
    {
        $this->em->flush();
    }

    public function delete(Recruteur $recruteur): void
    {
        $this->em->remove($recruteur);
        $this->em->flush();
    }
}