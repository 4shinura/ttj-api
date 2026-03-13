<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 400)]
    private ?string $contenu_Message = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateEnvoi_Message = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $emetteur_Message = null;

    #[ORM\ManyToOne(inversedBy: 'receivedMessages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $destinataire_Message = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenuMessage(): ?string
    {
        return $this->contenu_Message;
    }

    public function setContenuMessage(string $contenu_Message): static
    {
        $this->contenu_Message = $contenu_Message;

        return $this;
    }

    public function getDateEnvoiMessage(): ?\DateTime
    {
        return $this->dateEnvoi_Message;
    }

    public function setDateEnvoiMessage(\DateTime $dateEnvoi_Message): static
    {
        $this->dateEnvoi_Message = $dateEnvoi_Message;

        return $this;
    }

    public function getEmetteurMessage(): ?Utilisateur
    {
        return $this->emetteur_Message;
    }

    public function setEmetteurMessage(?Utilisateur $emetteur_Message): static
    {
        $this->emetteur_Message = $emetteur_Message;

        return $this;
    }

    public function getDestinataireMessage(): ?Utilisateur
    {
        return $this->destinataire_Message;
    }

    public function setDestinataireMessage(?Utilisateur $destinataire_Message): static
    {
        $this->destinataire_Message = $destinataire_Message;

        return $this;
    }
}
