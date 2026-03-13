<?php

namespace App\Controller\Api;

use App\Entity\Candidature;
use App\Repository\CandidatRepository;
use App\Repository\OffreRepository;
use App\Service\CandidatureService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/candidatures')]
class CandidatureController extends AbstractController
{
    public function __construct(
        private CandidatureService $service,
        private CandidatRepository $candidatRepo,
        private OffreRepository $offreRepo
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $candidatures = $this->service->getAll();

        $data = array_map(fn($c) => [
            'id' => $c->getId(),
            'candidat' => $c->getCandidat()->getId(),
            'offre' => $c->getOffre()->getId(),
            'date' => $c->getDateCandidature()?->format('Y-m-d'),
            'statut' => $c->getStatutCandidature()
        ], $candidatures);

        return $this->json($data);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $candidat = $this->candidatRepo->find($data['candidat_id']);
        $offre = $this->offreRepo->find($data['offre_id']);

        if (!$candidat || !$offre) {
            return $this->json(['error' => 'Candidat ou offre invalide'], 400);
        }

        $candidature = new Candidature();

        $candidature->setCandidat($candidat);
        $candidature->setOffre($offre);
        $candidature->setDateCandidature(new \DateTime());
        $candidature->setStatutCandidature($data['statut']);

        $this->service->create($candidature);

        return $this->json([
            'id' => $candidature->getId()
        ], 201);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $candidature = $this->service->getAll();

        if (!$candidature) {
            return $this->json(['error' => 'Candidature non trouvée'], 404);
        }

        $this->service->delete($candidature);

        return $this->json([
            'message' => 'Candidature supprimée'
        ]);
    }
}