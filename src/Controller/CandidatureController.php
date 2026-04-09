<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Repository\CandidatRepository;
use App\Repository\OffreRepository;
use App\Service\AuthService;
use App\Service\CandidatureService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route("/api")]
final class CandidatureController extends AbstractController
{
    public function __construct(
        private CandidatureService $service,
        private CandidatRepository $candidatRepo,
        private OffreRepository $offreRepo,
        private AuthService $authService
    ) {}

    private function mapCandidatureToArray(Candidature $c): array
    {
        return [
            'id' => $c->getId(),
            'candidat' => [
                'id' => $c->getCandidat()->getId(),
                'nom' => $c->getCandidat()->getNomUtilisateur(),
                'prenom' => $c->getCandidat()->getPrenomUtilisateur(),
                'email' => $c->getCandidat()->getEmailUtilisateur(),
            ],
            'offre' => [
                'id' => $c->getOffre()->getId(),
                'type' => $c->getOffre()->getTypeOffre(),
                'titre' => $c->getOffre()->getTitreOffre(),
                'description' => $c->getOffre()->getDescriptionOffre(),
                'datePublication' => $c->getOffre()->getDatePublicationOffre()?->format('Y-m-d'),
                'dateLimite' => $c->getOffre()->getDateLimiteOffre()?->format('Y-m-d'),
                'statut' => $c->getOffre()->getStatutOffre(),
            ],
            'date' => $c->getDateCandidature()?->format('Y-m-d'),
            'statut' => $c->getStatutCandidature()
        ];
    }

    #[Route('/recruteurs/offres/{offreId}/candidatures', name: 'recruteur_offre_candidatures', methods: ['GET'])]
    public function listForRecruteur(Request $request, int $offreId): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isRecruteur($idUser)) {
            return $this->json(['error' => 'Accès refusé : utilisateur non recruteur'], 403);
        }

        $offre = $this->offreRepo->find($offreId);
        if (!$offre) {
            return $this->json(['error' => 'Offre non trouvée'], 404);
        }

        $recruteurOffre = $offre->getRecruteurOffre();
        if (!$recruteurOffre || $recruteurOffre->getId() !== $idUser) {
            return $this->json(['error' => 'Accès refusé : cette offre n\'appartient pas au recruteur'], 403);
        }

        $candidatures = $this->service->getByOffre($offreId);
        $data = array_map([$this, 'mapCandidatureToArray'], $candidatures);

        return $this->json($data);
    }

    #[Route('/recruteurs/candidatures/{id}/statut', name: 'recruteur_candidature_update_statut', methods: ['PUT'])]
    public function updateStatut(Request $request, int $id): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isRecruteur($idUser)) {
            return $this->json(['error' => 'Accès refusé : utilisateur non recruteur'], 403);
        }

        $candidature = $this->service->getById($id);
        if (!$candidature) {
            return $this->json(['error' => 'Candidature non trouvée'], 404);
        }

        $offre = $candidature->getOffre();
        $recruteurOffre = $offre->getRecruteurOffre();
        if (!$recruteurOffre || $recruteurOffre->getId() !== $idUser) {
            return $this->json(['error' => 'Accès refusé : cette candidature n\'appartient pas au recruteur'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['statut']) || !is_string($data['statut'])) {
            return $this->json(['error' => 'Payload invalide : statut requis'], 400);
        }

        $candidature->setStatutCandidature($data['statut']);
        $this->service->update($candidature);

        return $this->json($this->mapCandidatureToArray($candidature));
    }

    #[Route('/candidats/candidatures', name: 'candidat_candidatures_list', methods: ['GET'])]
    public function listForCandidat(Request $request): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
       
        if (!$this->authService->isCandidat($idUser)) {
            return $this->json(['error' => 'Accès refusé : utilisateur non candidat'], 403);
        }

        $candidatures = $this->service->getByCandidat($idUser);
        $data = array_map([$this, 'mapCandidatureToArray'], $candidatures);

        return $this->json($data);
    }

    #[Route('/candidats/candidatures/{id}', name: 'candidat_candidature_show', methods: ['GET'])]
    public function showForCandidat(Request $request, int $id): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isCandidat($idUser)) {
            return $this->json(['error' => 'Accès refusé : utilisateur non candidat'], 403);
        }

        $candidature = $this->service->getById($id);
        if (!$candidature) {
            return $this->json(['error' => 'Candidature non trouvée'], 404);
        }

        if ($candidature->getCandidat()->getId() !== $idUser) {
            return $this->json(['error' => 'Accès refusé : cette candidature ne vous appartient pas'], 403);
        }

        return $this->json($this->mapCandidatureToArray($candidature));
    }

    #[Route('/candidats/candidatures', name: 'candidat_candidature_create', methods: ['POST'])]
    public function createForCandidat(Request $request): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isCandidat($idUser)) {
            return $this->json(['error' => 'Accès refusé : utilisateur non candidat'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['offre_id']) || !is_int($data['offre_id'])) {
            return $this->json(['error' => 'Payload invalide : offre_id requis'], 400);
        }

        $candidat = $this->candidatRepo->find($idUser);
        $offre = $this->offreRepo->find($data['offre_id']);

        if (!$candidat || !$offre) {
            return $this->json(['error' => 'Candidat ou offre invalide'], 400);
        }

        // Vérifier si une candidature existe déjà pour cette offre et ce candidat
        $existingCandidatures = $this->service->getByCandidat($idUser);
        foreach ($existingCandidatures as $existing) {
            if ($existing->getOffre()->getId() === $data['offre_id']) {
                return $this->json(['error' => 'Vous avez déjà postulé à cette offre'], 409);
            }
        }

        $candidature = new Candidature();
        $candidature->setCandidat($candidat);
        $candidature->setOffre($offre);
        $candidature->setDateCandidature(new \DateTime());
        $candidature->setStatutCandidature('pending');

        $this->service->create($candidature);

        return $this->json($this->mapCandidatureToArray($candidature), 201);
    }

    #[Route('/candidats/candidatures/{id}', name: 'candidat_candidature_delete', methods: ['DELETE'])]
    public function deleteForCandidat(Request $request, int $id): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $idUser = (int) ($response['userId'] ?? null);
        if (!$this->authService->isCandidat($idUser)) {
            return $this->json(['error' => 'Accès refusé : utilisateur non candidat'], 403);
        }

        $candidature = $this->service->getById($id);
        if (!$candidature) {
            return $this->json(['error' => 'Candidature non trouvée'], 404);
        }

        if ($candidature->getCandidat()->getId() !== $idUser) {
            return $this->json(['error' => 'Accès refusé : cette candidature ne vous appartient pas'], 403);
        }

        $this->service->delete($candidature);

        return $this->json(['message' => 'Candidature supprimée']);
    }
}