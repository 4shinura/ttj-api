<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

        if (!password_verify($password, $user->getMdpUtilisateur())) {
            return null;
        }

        return $user;
    }

    public function getConnectedUser(Request $request): JsonResponse|array
    {
        $token = $request->cookies->get('access_token');

        if (!$token) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        // $user = $authService->getUserFromToken($token);

        // if (!$user) {
        //     return new JsonResponse(['error' => 'Token invalide ou expiré'], 401);
        // }

        return ['userId' => $token];
    }

    public function isRecruteur(int $id): bool
    {
        // $recruteur = $this->repository->findByEmail($email);

        // if (!$recruteur) {
        //     return null;
        // }

        // return true|false;
    }

    

    // public function logout(): void
    // {
    // }

    // public function getCurrentUserId(): ?int
    // {
    // }
}