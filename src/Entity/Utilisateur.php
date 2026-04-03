<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "type", type: "string")]
#[ORM\DiscriminatorMap([
    "candidat" => Candidat::class,
    "recruteur" => Recruteur::class,
    "administrateur" => Administrateur::class,
])]
#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom_Utilisateur = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom_Utilisateur = null;

    #[ORM\Column(length: 100)]
    private ?string $email_Utilisateur = null;

    #[ORM\Column(length: 255)]
    private ?string $mdp_Utilisateur = null;

    #[ORM\Column(length: 50)]
    private ?string $statut_Utilisateur = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'emetteur_Message')]
    private Collection $messages;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'destinataire_Message')]
    private Collection $receivedMessages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomUtilisateur(): ?string
    {
        return $this->nom_Utilisateur;
    }

    public function setNomUtilisateur(string $nom_Utilisateur): static
    {
        $this->nom_Utilisateur = $nom_Utilisateur;

        return $this;
    }

    public function getPrenomUtilisateur(): ?string
    {
        return $this->prenom_Utilisateur;
    }

    public function setPrenomUtilisateur(string $prenom_Utilisateur): static
    {
        $this->prenom_Utilisateur = $prenom_Utilisateur;

        return $this;
    }

    public function getEmailUtilisateur(): ?string
    {
        return $this->email_Utilisateur;
    }

    public function setEmailUtilisateur(string $email_Utilisateur): static
    {
        $this->email_Utilisateur = $email_Utilisateur;

        return $this;
    }

    public function getMdpUtilisateur(): ?string
    {
        return $this->mdp_Utilisateur;
    }

    public function setMdpUtilisateur(string $mdp_Utilisateur): static
    {
        $this->mdp_Utilisateur = $mdp_Utilisateur;

        return $this;
    }

    public function getStatutUtilisateur(): ?string
    {
        return $this->statut_Utilisateur;
    }

    public function setStatutUtilisateur(string $statut_Utilisateur): static
    {
        $this->statut_Utilisateur = $statut_Utilisateur;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setEmetteurMessage($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getEmetteurMessage() === $this) {
                $message->setEmetteurMessage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function addReceivedMessage(Message $receivedMessage): static
    {
        if (!$this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages->add($receivedMessage);
            $receivedMessage->setDestinataireMessage($this);
        }

        return $this;
    }

    public function removeReceivedMessage(Message $receivedMessage): static
    {
        if ($this->receivedMessages->removeElement($receivedMessage)) {
            // set the owning side to null (unless already changed)
            if ($receivedMessage->getDestinataireMessage() === $this) {
                $receivedMessage->setDestinataireMessage(null);
            }
        }

        return $this;
    }
}
