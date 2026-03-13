<?php
namespace App\Service;

use App\Entity\Entreprise;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;

class EntrepriseService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EntrepriseRepository $repository
    ) {}

    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    public function getById(int $id): ?Entreprise
    {
        return $this->repository->find($id);
    }

    public function create(array $data): Entreprise
    {
        $ent = new Entreprise();
        $ent->setRaisonSocialeEntreprise($data['raison'])
            ->setAdresseEntreprise($data['adresse'])
            ->setTelEntreprise($data['tel']);

        $this->em->persist($ent);
        $this->em->flush();

        return $ent;
    }

    public function update(Entreprise $ent, array $data): Entreprise
    {
        $ent->setRaisonSocialeEntreprise($data['raison'] ?? $ent->getRaisonSocialeEntreprise())
            ->setAdresseEntreprise($data['adresse'] ?? $ent->getAdresseEntreprise())
            ->setTelEntreprise($data['tel'] ?? $ent->getTelEntreprise());

        $this->em->flush();
        return $ent;
    }

    public function delete(Entreprise $ent): void
    {
        $this->em->remove($ent);
        $this->em->flush();
    }
}