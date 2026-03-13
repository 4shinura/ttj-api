<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Offre;
use App\Service\OffreService;

#[Route("/api/offres")]
final class OffreController extends AbstractController
{
    private OffreService $service;

    public function __construct(OffreService $service)
    {
        $this->service = $service;
    }

    // LIST
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function getOffres(): JsonResponse
    {
        $offres = $this->service->getOffres();
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

    // READ
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function getOffre(int $id): JsonResponse
    {
        $offre = $this->service->getOffre($id);
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

    // CREATE
    #[Route('', name: 'create', methods: ['POST'])]
    public function createOffre(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $offre = $this->service->create($data);

        return $this->json(['id' => $offre->getId()], 201);
    }

    // UPDATE
    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function updateOffre(Request $request, int $id): JsonResponse
    {
        $offre = $this->service->getOffre($id);
        if (!$offre) return $this->json(['error' => 'Offre non trouvée'], 404);

        $data = json_decode($request->getContent(), true);
        $this->service->update($offre, $data);

        return $this->json(['success' => true]);
    }

    // DELETE
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteOffre(int $id): JsonResponse
    {
        $offre = $this->service->getOffre($id);
        if (!$offre) return $this->json(['error' => 'Offre non trouvée'], 404);

        $this->service->delete($offre);
        return $this->json(['success' => true]);
    }
}