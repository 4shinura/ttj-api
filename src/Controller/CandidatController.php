<?php

namespace App\Controller\Api;

use App\Service\CandidatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/candidats')]
class CandidatController extends AbstractController
{
    public function __construct(private CandidatService $service)
    {
    }

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $candidats = $this->service->getAll();

        $data = array_map(fn($c) => [
            'id' => $c->getId(),
            'nom' => $c->getNomUtilisateur(),
            'prenom' => $c->getPrenomUtilisateur(),
            'email' => $c->getEmailUtilisateur(),
        ], $candidats);

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $c = $this->service->getById($id);

        if (!$c) {
            return $this->json(['error' => 'Candidat non trouvé'], 404);
        }

        return $this->json([
            'id' => $c->getId(),
            'nom' => $c->getNomUtilisateur(),
            'prenom' => $c->getPrenomUtilisateur(),
            'email' => $c->getEmailUtilisateur(),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $candidat = $this->service->create($data);

        return $this->json([
            'id' => $candidat->getId()
        ], 201);
    }
}