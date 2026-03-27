<?php

namespace App\Service;

use App\Entity\Candidat;
use App\Entity\Recruteur;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthService
{
    private UtilisateurRepository $repository;
    private EntityManagerInterface $em;

    public function __construct(
        UtilisateurRepository $repository,
        EntityManagerInterface $em
    ) {
        $this->repository = $repository;
        $this->em = $em;
    }

    public function login(string $email, string $password): Utilisateur|null
    {
        $user = $this->repository->findByEmail($email);

        if (!$user) {
            return null;
        }

        $hashedPassword = $user->getMdpUtilisateur();

        // Vérifier d'abord si le mot de passe est haché
        if (password_verify($password, $hashedPassword)) {
            return $user;
        }

        // Si vérification échoue, vérifier si c'est un mot de passe en clair
        if ($password === $hashedPassword) {
            // Hacher le mot de passe et le sauvegarder en base
            $newHashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $user->setMdpUtilisateur($newHashedPassword);
            $this->em->flush();
            return $user;
        }

        return null;
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
        $user = $this->repository->find($id);

        return $user instanceof Recruteur;
    }

    public function isCandidat(int $id): bool
    {
        $user = $this->repository->find($id);

        return $user instanceof Candidat;
    }

    public function isAdmin(int $id): bool
    {
        $user = $this->repository->find($id);

        return $user && $user->getStatutUtilisateur() === 'admin';
    }

    // public function getCurrentUserId(): ?int
    // {
    // }
}