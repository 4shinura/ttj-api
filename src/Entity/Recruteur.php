<?php

namespace App\Entity;

use App\Repository\RecruteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecruteurRepository::class)]
class Recruteur extends Utilisateur
{
    #[ORM\ManyToOne(inversedBy: 'recruteurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise_Recruteur = null;

    /**
     * @var Collection<int, Offre>
     */
    #[ORM\OneToMany(targetEntity: Offre::class, mappedBy: 'recruteur_Offre')]
    private Collection $offres;

    public function __construct()
    {
        parent::__construct();
        $this->offres = new ArrayCollection();
    }

    public function getEntrepriseRecruteur(): ?Entreprise
    {
        return $this->entreprise_Recruteur;
    }

    public function setEntrepriseRecruteur(?Entreprise $entreprise_Recruteur): static
    {
        $this->entreprise_Recruteur = $entreprise_Recruteur;

        return $this;
    }

    /**
     * @return Collection<int, Offre>
     */
    public function getOffres(): Collection
    {
        return $this->offres;
    }

    public function addOffre(Offre $offre): static
    {
        if (!$this->offres->contains($offre)) {
            $this->offres->add($offre);
            $offre->setRecruteurOffre($this);
        }

        return $this;
    }

    public function removeOffre(Offre $offre): static
    {
        if ($this->offres->removeElement($offre)) {
            // set the owning side to null (unless already changed)
            if ($offre->getRecruteurOffre() === $this) {
                $offre->setRecruteurOffre(null);
            }
        }

        return $this;
    }
}
