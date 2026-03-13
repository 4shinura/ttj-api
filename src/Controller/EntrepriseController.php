<?php
namespace App\Controller;

use App\Entity\Entreprise;
use App\Service\EntrepriseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/entreprises', name: 'api_entreprises_')]
class EntrepriseController extends AbstractController
{
    public function __construct(private EntrepriseService $service) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $entreprises = $this->service->getAll();
        $data = array_map(fn(Entreprise $e) => [
            'id' => $e->getId(),
            'raison' => $e->getRaisonSocialeEntreprise(),
            'adresse' => $e->getAdresseEntreprise(),
            'tel' => $e->getTelEntreprise()
        ], $entreprises);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $ent = $this->service->getById($id);
        if (!$ent) return $this->json(['error' => 'Entreprise non trouvée'], 404);

        return $this->json([
            'id' => $ent->getId(),
            'raison' => $ent->getRaisonSocialeEntreprise(),
            'adresse' => $ent->getAdresseEntreprise(),
            'tel' => $ent->getTelEntreprise()
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ent = $this->service->create($data);

        return $this->json(['id' => $ent->getId()], 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $ent = $this->service->getById($id);
        if (!$ent) return $this->json(['error' => 'Entreprise non trouvée'], 404);

        $data = json_decode($request->getContent(), true);
        $this->service->update($ent, $data);

        return $this->json(['status' => 'ok']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $ent = $this->service->getById($id);
        if (!$ent) return $this->json(['error' => 'Entreprise non trouvée'], 404);

        $this->service->delete($ent);
        return $this->json(['status' => 'deleted']);
    }
}