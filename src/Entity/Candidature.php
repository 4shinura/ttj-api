<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'candidatures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Candidat $candidat = null;

    #[ORM\ManyToOne(inversedBy: 'candidatures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offre $offre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_Candidature = null;

    #[ORM\Column(length: 50)]
    private ?string $statut_Candidature = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidat(): ?Candidat
    {
        return $this->candidat;
    }

    public function setCandidat(?Candidat $candidat): static
    {
        $this->candidat = $candidat;

        return $this;
    }

    public function getOffre(): ?Offre
    {
        return $this->offre;
    }

    public function setOffre(?Offre $offre): static
    {
        $this->offre = $offre;

        return $this;
    }

    public function getDateCandidature(): ?\DateTime
    {
        return $this->date_Candidature;
    }

    public function setDateCandidature(\DateTime $date_Candidature): static
    {
        $this->date_Candidature = $date_Candidature;

        return $this;
    }

    public function getStatutCandidature(): ?string
    {
        return $this->statut_Candidature;
    }

    public function setStatutCandidature(string $statut_Candidature): static
    {
        $this->statut_Candidature = $statut_Candidature;

        return $this;
    }
}
