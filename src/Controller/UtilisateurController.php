<?php
namespace App\Controller;

use App\Service\UtilisateurService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/utilisateurs', name: 'api_utilisateurs_')]
class UtilisateurController extends AbstractController
{
    public function __construct(private UtilisateurService $service) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = $this->service->getAll();
        $data = array_map(fn($u) => [
            'id' => $u->getId(),
            'nom' => $u->getNomUtilisateur(),
            'prenom' => $u->getPrenomUtilisateur(),
            'email' => $u->getEmailUtilisateur(),
            'statut' => $u->getStatutUtilisateur()
        ], $users);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->service->getById($id);
        if (!$user) return $this->json(['error' => 'Utilisateur non trouvé'], 404);

        return $this->json([
            'id' => $user->getId(),
            'nom' => $user->getNomUtilisateur(),
            'prenom' => $user->getPrenomUtilisateur(),
            'email' => $user->getEmailUtilisateur(),
            'statut' => $user->getStatutUtilisateur()
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->service->create($data);

        return $this->json([
            'id' => $user->getId()
        ], 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $this->service->getById($id);
        if (!$user) return $this->json(['error' => 'Utilisateur non trouvé'], 404);

        $data = json_decode($request->getContent(), true);
        $user = $this->service->update($user, $data);

        return $this->json(['status' => 'ok']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->service->getById($id);
        if (!$user) return $this->json(['error' => 'Utilisateur non trouvé'], 404);

        $this->service->delete($user);
        return $this->json(['status' => 'deleted']);
    }
}