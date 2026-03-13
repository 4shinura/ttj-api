<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $type_Offre = null;

    #[ORM\Column(length: 50)]
    private ?string $titre_Offre = null;

    #[ORM\Column(length: 400)]
    private ?string $description_Offre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $datePublication_Offre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateLimite_Offre = null;

    #[ORM\Column(length: 50)]
    private ?string $statut_Offre = null;

    #[ORM\ManyToOne(inversedBy: 'offres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recruteur $recruteur_Offre = null;

    /**
     * @var Collection<int, Candidature>
     */
    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'offre')]
    private Collection $candidatures;

    public function __construct()
    {
        $this->candidatures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeOffre(): ?string
    {
        return $this->type_Offre;
    }

    public function setTypeOffre(string $type_Offre): static
    {
        $this->type_Offre = $type_Offre;

        return $this;
    }

    public function getTitreOffre(): ?string
    {
        return $this->titre_Offre;
    }

    public function setTitreOffre(string $titre_Offre): static
    {
        $this->titre_Offre = $titre_Offre;

        return $this;
    }

    public function getDescriptionOffre(): ?string
    {
        return $this->description_Offre;
    }

    public function setDescriptionOffre(string $description_Offre): static
    {
        $this->description_Offre = $description_Offre;

        return $this;
    }

    public function getDatePublicationOffre(): ?\DateTime
    {
        return $this->datePublication_Offre;
    }

    public function setDatePublicationOffre(\DateTime $datePublication_Offre): static
    {
        $this->datePublication_Offre = $datePublication_Offre;

        return $this;
    }

    public function getDateLimiteOffre(): ?\DateTime
    {
        return $this->dateLimite_Offre;
    }

    public function setDateLimiteOffre(\DateTime $dateLimite_Offre): static
    {
        $this->dateLimite_Offre = $dateLimite_Offre;

        return $this;
    }

    public function getStatutOffre(): ?string
    {
        return $this->statut_Offre;
    }

    public function setStatutOffre(string $statut_Offre): static
    {
        $this->statut_Offre = $statut_Offre;

        return $this;
    }

    public function getRecruteurOffre(): ?Recruteur
    {
        return $this->recruteur_Offre;
    }

    public function setRecruteurOffre(?Recruteur $recruteur_Offre): static
    {
        $this->recruteur_Offre = $recruteur_Offre;

        return $this;
    }

    /**
     * @return Collection<int, Candidature>
     */
    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function addCandidature(Candidature $candidature): static
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures->add($candidature);
            $candidature->setOffre($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidatures->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getOffre() === $this) {
                $candidature->setOffre(null);
            }
        }

        return $this;
    }
}
