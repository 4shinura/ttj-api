<?php

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    private AuthService $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return $this->json(['error' => 'Email et mot de passe requis'], 400);
        }

        $user = $this->service->login($email, $password);

        if (!$user) {
            return $this->json(['error' => 'Identifiants invalides'], 401);
        }

        return $this->json([
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user->getId(),
                'nom' => $user->getNomUtilisateur(),
                'prenom' => $user->getPrenomUtilisateur(),
                'email' => $user->getEmailUtilisateur()
            ]
        ]);
    }

    // #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    // public function logout(): JsonResponse
    // {
    //     $this->service->logout();

    //     return $this->json([
    //         'message' => 'Déconnexion réussie'
    //     ]);
    // }
}