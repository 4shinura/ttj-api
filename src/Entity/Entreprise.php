<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $raisonSociale_Entreprise = null;

    #[ORM\Column(length: 200)]
    private ?string $adresse_Entreprise = null;

    #[ORM\Column]
    private ?int $tel_Entreprise = null;

    /**
     * @var Collection<int, Recruteur>
     */
    #[ORM\OneToMany(targetEntity: Recruteur::class, mappedBy: 'entreprise_Recruteur')]
    private Collection $recruteurs;

    public function __construct()
    {
        $this->recruteurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaisonSocialeEntreprise(): ?string
    {
        return $this->raisonSociale_Entreprise;
    }

    public function setRaisonSocialeEntreprise(string $raisonSociale_Entreprise): static
    {
        $this->raisonSociale_Entreprise = $raisonSociale_Entreprise;

        return $this;
    }

    public function getAdresseEntreprise(): ?string
    {
        return $this->adresse_Entreprise;
    }

    public function setAdresseEntreprise(string $adresse_Entreprise): static
    {
        $this->adresse_Entreprise = $adresse_Entreprise;

        return $this;
    }

    public function getTelEntreprise(): ?int
    {
        return $this->tel_Entreprise;
    }

    public function setTelEntreprise(int $tel_Entreprise): static
    {
        $this->tel_Entreprise = $tel_Entreprise;

        return $this;
    }

    /**
     * @return Collection<int, Recruteur>
     */
    public function getRecruteurs(): Collection
    {
        return $this->recruteurs;
    }

    public function addRecruteur(Recruteur $recruteur): static
    {
        if (!$this->recruteurs->contains($recruteur)) {
            $this->recruteurs->add($recruteur);
            $recruteur->setEntrepriseRecruteur($this);
        }

        return $this;
    }

    public function removeRecruteur(Recruteur $recruteur): static
    {
        if ($this->recruteurs->removeElement($recruteur)) {
            // set the owning side to null (unless already changed)
            if ($recruteur->getEntrepriseRecruteur() === $this) {
                $recruteur->setEntrepriseRecruteur(null);
            }
        }

        return $this;
    }
}
