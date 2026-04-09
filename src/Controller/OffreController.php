<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Offre;
use App\Service\OffreService;
use App\Service\AuthService;
use Exception;

#[Route("/api")]
final class OffreController extends AbstractController
{
    private OffreService $service;
    private AuthService $authService;

    public function __construct(OffreService $service, AuthService $authService)
    {
        $this->service = $service;
        $this->authService = $authService;
    }

    // LIST
    #[Route('/offres', name: 'list', methods: ['GET'])]
    public function getOffres(): JsonResponse
    {
        $offres = $this->service->getPublishedOffres();
        $data = array_map(fn(Offre $o) => [
            'id' => $o->getId(),
            'type' => $o->getTypeOffre(),
            'titre' => $o->getTitreOffre(),
            'description' => $o->getDescriptionOffre(),
            'datePublication' => $o->getDatePublicationOffre()?->format('Y-m-d'),
            'dateLimite' => $o->getDateLimiteOffre()?->format('Y-m-d'),
            'statut' => $o->getStatutOffre(),
        ], $offres);

        return $this->json($data);
    }

    #[Route('/offres/{id}', name: 'show_published', methods: ['GET'])]
    public function getPublishedOffre(int $id): JsonResponse
    {
        $offre = $this->service->getPublishedOffre($id);
        if (!$offre) return $this->json(['error' => 'Offre non trouvée'], 404);

        return $this->json([
            'id' => $offre->getId(),
            'type' => $offre->getTypeOffre(),
            'titre' => $offre->getTitreOffre(),
            'description' => $offre->getDescriptionOffre(),
            'datePublication' => $offre->getDatePublicationOffre()?->format('Y-m-d'),
            'dateLimite' => $offre->getDateLimiteOffre()?->format('Y-m-d'),
            'statut' => $offre->getStatutOffre(),
        ]);
    }

        #[Route('/recruteurs/offres', name: 'offres_recruteur', methods: ['GET'])]
    public function voirOffresRecruteur(Request $request): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isRecruteur($idUser)){
            return $this->json(['error' => 'Accès refusé : utilisateur non recruteur'], 403);
        }
        
        $offres = $this->service->getOffresByRecruteur($idUser);
        $data = array_map(fn(Offre $o) => [
            'id' => $o->getId(),
            'type' => $o->getTypeOffre(),
            'titre' => $o->getTitreOffre(),
            'description' => $o->getDescriptionOffre(),
            'datePublication' => $o->getDatePublicationOffre()?->format('Y-m-d'),
            'dateLimite' => $o->getDateLimiteOffre()?->format('Y-m-d'),
            'statut' => $o->getStatutOffre(),
        ], $offres);

        return $this->json($data);
    }

    #[Route('/recruteurs/offres/{id}', name: 'offre_recruteur', methods: ['GET'])]
    public function voirOffreRecruteur(Request $request, int $id): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isRecruteur($idUser)) {
            return $this->json(['error' => 'Accès refusé : utilisateur non recruteur'], 403);
        }

        $offre = $this->service->getOffre($id);
        if (!$offre) {
            return $this->json(['error' => 'Offre non trouvée'], 404);
        }

        $recruteurOffre = $offre->getRecruteurOffre();
        if (!$recruteurOffre || $recruteurOffre->getId() !== $idUser) {
            return $this->json(['error' => 'Accès refusé : cette offre n\'appartient pas au recruteur'], 403);
        }

        $data = [
            'id' => $offre->getId(),
            'type' => $offre->getTypeOffre(),
            'titre' => $offre->getTitreOffre(),
            'description' => $offre->getDescriptionOffre(),
            'datePublication' => $offre->getDatePublicationOffre()?->format('Y-m-d'),
            'dateLimite' => $offre->getDateLimiteOffre()?->format('Y-m-d'),
            'statut' => $offre->getStatutOffre(),
        ];

        return $this->json($data);
    }

    // CREATE
    #[Route('/recruteurs/offres', name: 'create', methods: ['POST'])]
    public function createOffre(Request $request): JsonResponse
    {
        $connected = $this->authService->getConnectedUser($request);
        $idUser = (int) ($connected['userId'] ?? 0);

        if (!$this->authService->isRecruteur($idUser)) {
            return $this->json(['error' => 'Accès refusé : utilisateur non recruteur'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Payload invalide'], 400);
        }

        $data['recruteur'] = $idUser;
        $offre = $this->service->create($data);

        return $this->json(['id' => $offre->getId()], 201);
    }

    // UPDATE
    #[Route('/recruteurs/offres/{id}', name: 'update', methods: ['PUT'])]
    public function updateOffre(Request $request, int $id): JsonResponse
    {
        $connected = $this->authService->getConnectedUser($request);
        $idUser = (int) ($connected['userId'] ?? 0);

        if (!$this->authService->isRecruteur($idUser)) {
            return $this->json(['error' => 'Accès refusé : utilisateur non recruteur'], 403);
        }

        $offre = $this->service->getOffre($id);
        if (!$offre) {
            return $this->json(['error' => 'Offre non trouvée'], 404);
        }

        $recruteurOffre = $offre->getRecruteurOffre();
        if (!$recruteurOffre || $recruteurOffre->getId() !== $idUser) {
            return $this->json(['error' => 'Accès refusé : cette offre n\'appartient pas au recruteur'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Payload invalide'], 400);
        }

        $this->service->update($offre, $data);

        return $this->json(['success' => true]);
    }

    // DELETE
    #[Route('/recruteurs/offres/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteOffre(Request $request, int $id): JsonResponse
    {
        $connected = $this->authService->getConnectedUser($request);
        $idUser = (int) ($connected['userId'] ?? 0);

        if (!$this->authService->isRecruteur($idUser)) {
            return $this->json(['error' => 'Accès refusé : utilisateur non recruteur'], 403);
        }

        $offre = $this->service->getOffre($id);
        if (!$offre) {
            return $this->json(['error' => 'Offre non trouvée'], 404);
        }

        $recruteurOffre = $offre->getRecruteurOffre();
        if (!$recruteurOffre || $recruteurOffre->getId() !== $idUser) {
            return $this->json(['error' => 'Accès refusé : cette offre n\'appartient pas au recruteur'], 403);
        }

        $this->service->delete($offre);
        return $this->json(['success' => true]);
    }

    // ADMIN - GET PENDING OFFERS
    #[Route('/admin/offres', name: 'admin_pending_offers', methods: ['GET'])]
    public function getOffresByStatus(Request $request): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isAdmin($idUser)){
            return $this->json(['error' => 'Accès refusé : utilisateur non administrateur'], 403);
        }

        $offres = $this->service->getOffresByStatus('pending');
        $data = array_map(fn(Offre $o) => [
            'id' => $o->getId(),
            'type' => $o->getTypeOffre(),
            'titre' => $o->getTitreOffre(),
            'description' => $o->getDescriptionOffre(),
            'datePublication' => $o->getDatePublicationOffre()?->format('Y-m-d'),
            'dateLimite' => $o->getDateLimiteOffre()?->format('Y-m-d'),
            'statut' => $o->getStatutOffre(),
        ], $offres);

        return $this->json($data);
    }

    // ADMIN - PUT PUBLISH PENDING OFFER
    #[Route('/admin/offres/{id}/publish', name: 'admin_publish_offre', methods: ['PUT'])]
    public function publishOffre(Request $request, int $id): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isAdmin($idUser)){
            return $this->json(['error' => 'Accès refusé : utilisateur non administrateur'], 403);
        }

        $offre = $this->service->getOffre($id);
        if (!$offre) {
            return $this->json(['error' => 'Offre non trouvée'], 404);
        }

        try {
            $this->service->publishOffre($offre);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json(['success' => true, 'id' => $offre->getId(), 'statut' => $offre->getStatutOffre()]);
    }
}