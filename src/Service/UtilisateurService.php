<?php
namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;

class UtilisateurService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UtilisateurRepository $repository
    ) {}

    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    public function getById(int $id): ?Utilisateur
    {
        return $this->repository->find($id);
    }

    public function create(array $data): Utilisateur
    {
        $user = new Utilisateur();
        $user->setNomUtilisateur($data['nom'])
             ->setPrenomUtilisateur($data['prenom'])
             ->setEmailUtilisateur($data['email'])
             ->setMdpUtilisateur($data['mdp'])
             ->setStatutUtilisateur($data['statut']);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function update(Utilisateur $user, array $data): Utilisateur
    {
        $user->setNomUtilisateur($data['nom'] ?? $user->getNomUtilisateur())
             ->setPrenomUtilisateur($data['prenom'] ?? $user->getPrenomUtilisateur())
             ->setEmailUtilisateur($data['email'] ?? $user->getEmailUtilisateur())
             ->setMdpUtilisateur($data['mdp'] ?? $user->getMdpUtilisateur())
             ->setStatutUtilisateur($data['statut'] ?? $user->getStatutUtilisateur());

        $this->em->flush();
        return $user;
    }

    public function delete(Utilisateur $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}