<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\Recruteur;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route("/api/admin")]
final class UtilisateurController extends AbstractController
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepo,
        private AuthService $authService
    ) {}

    #[Route('/utilisateurs', name: 'admin_utilisateurs_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isAdmin($idUser)) {
            return $this->json(['error' => 'Accès refusé : droits administrateur requis'], 403);
        }

        $utilisateurs = $this->utilisateurRepo->findAll();
        $data = array_map([$this, 'mapUtilisateurToArray'], $utilisateurs);

        return $this->json($data);
    }

    #[Route('/utilisateurs/{id}', name: 'admin_utilisateur_show', methods: ['GET'])]
    public function show(Request $request, int $id): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isAdmin($idUser)) {
            return $this->json(['error' => 'Accès refusé : droits administrateur requis'], 403);
        }

        $utilisateur = $this->utilisateurRepo->find($id);
        if (!$utilisateur) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        return $this->json($this->mapUtilisateurToArray($utilisateur));
    }

    #[Route('/registers/', name: 'admin_registers_pending', methods: ['GET'])]
    public function listPendingRegistrations(Request $request): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isAdmin($idUser)) {
            return $this->json(['error' => 'Accès refusé : droits administrateur requis'], 403);
        }

        $utilisateurs = $this->utilisateurRepo->findByStatus('pending');
        $data = array_map([$this, 'mapUtilisateurToArray'], $utilisateurs);

        return $this->json($data);
    }

    #[Route('/utilisateurs', name: 'admin_utilisateur_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isAdmin($idUser)) {
            return $this->json(['error' => 'Accès refusé : droits administrateur requis'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Payload invalide'], 400);
        }

        $requiredFields = ['nom', 'prenom', 'email', 'motDePasse', 'type'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return $this->json(['error' => "Champ requis manquant : $field"], 400);
            }
        }

        if (!in_array($data['type'], ['candidat', 'recruteur'])) {
            return $this->json(['error' => 'Type invalide : doit être "candidat" ou "recruteur"'], 400);
        }

        // Vérifier si email existe déjà
        $existingUser = $this->utilisateurRepo->findByEmail($data['email']);
        if ($existingUser) {
            return $this->json(['error' => 'Email déjà utilisé'], 409);
        }

        if ($data['type'] === 'candidat') {
            $utilisateur = new Candidat();
        } else {
            $utilisateur = new Recruteur();
        }

        $utilisateur->setNomUtilisateur($data['nom']);
        $utilisateur->setPrenomUtilisateur($data['prenom']);
        $utilisateur->setEmailUtilisateur($data['email']);
        $utilisateur->setMdpUtilisateur(password_hash($data['motDePasse'], PASSWORD_BCRYPT));
        $utilisateur->setStatutUtilisateur('actif');

        $this->utilisateurRepo->getEntityManager()->persist($utilisateur);
        $this->utilisateurRepo->getEntityManager()->flush();

        return $this->json($this->mapUtilisateurToArray($utilisateur), 201);
    }

    #[Route('/utilisateurs/{id}', name: 'admin_utilisateur_update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isAdmin($idUser)) {
            return $this->json(['error' => 'Accès refusé : droits administrateur requis'], 403);
        }

        $utilisateur = $this->utilisateurRepo->find($id);
        if (!$utilisateur) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Payload invalide'], 400);
        }

        if (isset($data['nom'])) $utilisateur->setNomUtilisateur($data['nom']);
        if (isset($data['prenom'])) $utilisateur->setPrenomUtilisateur($data['prenom']);
        if (isset($data['email'])) {
            // Vérifier si email existe déjà pour un autre utilisateur
            $existingUser = $this->utilisateurRepo->findByEmail($data['email']);
            if ($existingUser && $existingUser->getId() !== $id) {
                return $this->json(['error' => 'Email déjà utilisé'], 409);
            }
            $utilisateur->setEmailUtilisateur($data['email']);
        }
        if (isset($data['motDePasse'])) {
            $utilisateur->setMdpUtilisateur(password_hash($data['motDePasse'], PASSWORD_BCRYPT));
        }
        if (isset($data['statut'])) $utilisateur->setStatutUtilisateur($data['statut']);

        $this->utilisateurRepo->getEntityManager()->flush();

        return $this->json($this->mapUtilisateurToArray($utilisateur));
    }

    #[Route('/utilisateurs/{id}', name: 'admin_utilisateur_delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isAdmin($idUser)) {
            return $this->json(['error' => 'Accès refusé : droits administrateur requis'], 403);
        }

        $utilisateur = $this->utilisateurRepo->find($id);
        if (!$utilisateur) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        // Empêcher la suppression d'un admin
        if ($utilisateur->getStatutUtilisateur() === 'admin') {
            return $this->json(['error' => 'Impossible de supprimer un administrateur'], 403);
        }

        $this->utilisateurRepo->getEntityManager()->remove($utilisateur);
        $this->utilisateurRepo->getEntityManager()->flush();

        return $this->json(['message' => 'Utilisateur supprimé']);
    }

    private function mapUtilisateurToArray(Utilisateur $user): array
    {
        $type = $user instanceof Candidat ? 'candidat' : ($user instanceof Recruteur ? 'recruteur' : 'inconnu');

        return [
            'id' => $user->getId(),
            'nom' => $user->getNomUtilisateur(),
            'prenom' => $user->getPrenomUtilisateur(),
            'email' => $user->getEmailUtilisateur(),
            'statut' => $user->getStatutUtilisateur(),
            'type' => $type,
        ];
    }

    #[Route('/registers/valider', name: 'admin_register_validate', methods: ['PUT'])]
    public function validateRegistration(Request $request): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isAdmin($idUser)) {
            return $this->json(['error' => 'Accès refusé : droits administrateur requis'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || empty($data['id'])) {
            return $this->json(['error' => 'Payload invalide : id requis'], 400);
        }

        $utilisateur = $this->utilisateurRepo->find((int) $data['id']);
        if (!$utilisateur) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        if (strtolower(trim((string) $utilisateur->getStatutUtilisateur())) !== 'pending') {
            return $this->json(['error' => 'Statut invalide : l’utilisateur n’est pas en attente'], 400);
        }

        $utilisateur->setStatutUtilisateur('actif');
        $this->utilisateurRepo->getEntityManager()->flush();

        return $this->json(['success' => true, 'id' => $utilisateur->getId(), 'statut' => $utilisateur->getStatutUtilisateur()]);
    }
}