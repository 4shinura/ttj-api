<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;

class AuthService
{
    private UtilisateurRepository $repository;

    public function __construct(
        UtilisateurRepository $repository
    ) {
        $this->repository = $repository;
    }

    public function login(string $email, string $password): Utilisateur|null
    {
        $user = $this->repository->findByEmail($email);

        if (!$user) {
            return null;
        }

        if (password_verify($password, $user->getMdpUtilisateur())) {
            return null;
        }

        return $user;
    }

    // public function logout(): void
    // {
    // }

    // public function getCurrentUserId(): ?int
    // {
    // }
}