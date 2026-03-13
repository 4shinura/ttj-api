<?php

namespace App\Service;

use App\Entity\Offre;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;

class OffreService
{
    private EntityManagerInterface $em;
    private OffreRepository $repo;

    public function __construct(EntityManagerInterface $em, OffreRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
    }

    public function getOffres(): array
    {
        return $this->repo->findAll();
    }

    public function getOffre(int $id): ?Offre
    {
        return $this->repo->find($id);
    }

    public function create(array $data): Offre
    {
        $offre = new Offre();
        $offre->setTypeOffre($data['type'] ?? '');
        $offre->setTitreOffre($data['titre'] ?? '');
        $offre->setDescriptionOffre($data['description'] ?? '');
        $offre->setDatePublicationOffre(new \DateTime($data['datePublication'] ?? 'now'));
        $offre->setDateLimiteOffre(new \DateTime($data['dateLimite'] ?? 'now'));
        $offre->setStatutOffre($data['statut'] ?? '');

        // ici tu peux gérer le recruteur si tu passes l'id
        if (!empty($data['recruteur'])) {
            $offre->setRecruteurOffre($data['recruteur']);
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
        if (isset($data['recruteur'])) $offre->setRecruteurOffre($data['recruteur']);

        $this->em->flush();

        return $offre;
    }

    public function delete(Offre $offre): void
    {
        $this->em->remove($offre);
        $this->em->flush();
    }
}