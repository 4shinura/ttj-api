<?php

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Exception;

#[Route("/api/auth")]
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

        if ($user->getStatutUtilisateur() !== 'actif') {
            return $this->json(['error' => 'Votre compte a été désactivé ou n\'a pas encore été approuvé'], 403);
        }

        $token = $this->service->jwt_generate([
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user->getId(),
                'nom' => $user->getNomUtilisateur(),
                'prenom' => $user->getPrenomUtilisateur(),
                'email' => $user->getEmailUtilisateur(),
                'statut' => $user->getStatutUtilisateur(),
                'type' => $user instanceof \App\Entity\Administrateur ? 'administrateur' : ($user instanceof \App\Entity\Recruteur ? 'recruteur' : 'candidat'),
            ]
        ]);

        return $this->json(['token' => $token]);
    }

    #[Route('/recruteur/register', name: 'recruteur_register', methods: ['POST'])]
    public function registerRecruteur(Request $request): JsonResponse
    {
        return $this->registerUser($request, 'recruteur');
    }

    #[Route('/candidat/register', name: 'candidat_register', methods: ['POST'])]
    public function registerCandidat(Request $request): JsonResponse
    {
        return $this->registerUser($request, 'candidat');
    }

    private function registerUser(Request $request, string $type): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Payload invalide'], 400);
        }

        $required = ['nom', 'prenom', 'email', 'motDePasse'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->json(['error' => "Champ requis manquant : $field"], 400);
            }
        }

        try {
            $user = $this->service->register(
                $type,
                trim($data['nom']),
                trim($data['prenom']),
                trim($data['email']),
                $data['motDePasse']
            );
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 409);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json([
            'id' => $user->getId(),
            'nom' => $user->getNomUtilisateur(),
            'prenom' => $user->getPrenomUtilisateur(),
            'email' => $user->getEmailUtilisateur(),
            'type' => $type,
            'statut' => $user->getStatutUtilisateur(),
        ], 201);
    }
}