<?php

namespace App\Service;

use App\Entity\Offre;
use App\Repository\OffreRepository;
use App\Repository\RecruteurRepository;
use Doctrine\ORM\EntityManagerInterface;

class OffreService
{
    private EntityManagerInterface $em;
    private OffreRepository $repo;
    private RecruteurRepository $recruteurRepository;

    public function __construct(EntityManagerInterface $em, OffreRepository $repo, RecruteurRepository $recruteurRepository)
    {
        $this->em = $em;
        $this->repo = $repo;
        $this->recruteurRepository = $recruteurRepository;
    }

    public function getOffre(int $id): ?Offre
    {
        return $this->repo->find($id);
    }

    public function getPublishedOffres(): array
    {
        return $this->repo->findPublished();
    }

    public function getPublishedOffre(int $id): ?Offre
    {
        return $this->repo->findPublishedById($id);
    }

    public function create(array $data): Offre
    {
        $offre = new Offre();
        $offre->setTypeOffre($data['type'] ?? '');
        $offre->setTitreOffre($data['titre'] ?? '');
        $offre->setDescriptionOffre($data['description'] ?? '');
        $offre->setDatePublicationOffre(new \DateTime($data['datePublication'] ?? 'now'));
        $offre->setDateLimiteOffre(new \DateTime($data['dateLimite'] ?? 'now'));
        $offre->setStatutOffre('pending');

        // ici tu peux gérer le recruteur si tu passes l'id
        if (!empty($data['recruteur'])) {
            $recruteur = $this->recruteurRepository->find($data['recruteur']);
            $offre->setRecruteurOffre($recruteur);
        }

        $this->em->persist($offre);
        $this->em->flush();

        return $offre;
    }

    public function update(Offre $offre, array $data): Offre
    {
        if (isset($data['type'])) $offre->setTypeOffre($data['type']);
        if (isset($data['titre'])) $offre->setTitreOffre($data['titre']);
        if (isset($data['description'])) $offre->setDescriptionOffre($data['description']);
        if (isset($data['datePublication'])) $offre->setDatePublicationOffre(new \DateTime($data['datePublication']));
        if (isset($data['dateLimite'])) $offre->setDateLimiteOffre(new \DateTime($data['dateLimite']));
        if (isset($data['statut'])) $offre->setStatutOffre($data['statut']);
        $recruteur = $this->recruteurRepository->find($data['recruteur']);
        if (isset($data['recruteur'])) $offre->setRecruteurOffre($recruteur);

        $this->em->flush();

        return $offre;
    }

    public function delete(Offre $offre): void
    {
        $this->em->remove($offre);
        $this->em->flush();
    }

    public function publishOffre(Offre $offre): Offre
    {
        $currentStatus = strtolower(trim($offre->getStatutOffre() ?? ''));
        if (!in_array($currentStatus, ['pending', 'en attente'], true)) {
            throw new \RuntimeException('Statut invalide : l’offre doit être en attente pour être publiée.');
        }

        $offre->setStatutOffre('published');
        $this->em->flush();

        return $offre;
    }

    public function getOffresByRecruteur($idUser)
    {
        // throw new \Exception("Offres du recruteur $idUser : ");
        return $this->repo->findBy(["recruteur_Offre" => $idUser]);
    }

    public function getOffresByStatus(string $status): array
    {
        return $this->repo->findByStatus($status);
    }
}