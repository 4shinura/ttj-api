<?php
namespace App\Controller;

use App\Entity\Recruteur;
use App\Service\RecruteurService;
use App\Service\EntrepriseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/recruteurs', name: 'api_recruteurs_')]
class RecruteurController extends AbstractController
{
    public function __construct(
        private RecruteurService $service,
        private EntrepriseService $entrepriseService
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $recruteurs = $this->service->getAll();
        $data = array_map(fn(Recruteur $r) => [
            'id' => $r->getId(),
            'nom' => $r->getNomUtilisateur(),
            'prenom' => $r->getPrenomUtilisateur(),
            'email' => $r->getEmailUtilisateur(),
            'statut' => $r->getStatutUtilisateur(),
            'entreprise' => $r->getEntrepriseRecruteur()?->getRaisonSocialeEntreprise()
        ], $recruteurs);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $r = $this->service->getById($id);
        if (!$r) return $this->json(['error' => 'Recruteur non trouvé'], 404);

        return $this->json([
            'id' => $r->getId(),
            'nom' => $r->getNomUtilisateur(),
            'prenom' => $r->getPrenomUtilisateur(),
            'email' => $r->getEmailUtilisateur(),
            'statut' => $r->getStatutUtilisateur(),
            'entreprise' => $r->getEntrepriseRecruteur()?->getRaisonSocialeEntreprise()
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $entreprise = $this->entrepriseService->getById($data['entreprise_id']);
        if (!$entreprise) return $this->json(['error' => 'Entreprise non trouvée'], 404);

        $recruteur = new Recruteur();
        $recruteur->setNomUtilisateur($data['nom'])
                  ->setPrenomUtilisateur($data['prenom'])
                  ->setEmailUtilisateur($data['email'])
                  ->setMdpUtilisateur($data['mdp'])
                  ->setStatutUtilisateur($data['statut'])
                  ->setEntrepriseRecruteur($entreprise);

        $this->service->create($recruteur);

        return $this->json(['id' => $recruteur->getId()], 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $recruteur = $this->service->getById($id);
        if (!$recruteur) return $this->json(['error' => 'Recruteur non trouvé'], 404);

        $data = json_decode($request->getContent(), true);
        if (!empty($data['entreprise_id'])) {
            $entreprise = $this->entrepriseService->getById($data['entreprise_id']);
            if ($entreprise) $recruteur->setEntrepriseRecruteur($entreprise);
        }

        $recruteur->setNomUtilisateur($data['nom'] ?? $recruteur->getNomUtilisateur())
                  ->setPrenomUtilisateur($data['prenom'] ?? $recruteur->getPrenomUtilisateur())
                  ->setEmailUtilisateur($data['email'] ?? $recruteur->getEmailUtilisateur())
                  ->setMdpUtilisateur($data['mdp'] ?? $recruteur->getMdpUtilisateur())
                  ->setStatutUtilisateur($data['statut'] ?? $recruteur->getStatutUtilisateur());

        $this->service->update();

        return $this->json(['status' => 'ok']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $r = $this->service->getById($id);
        if (!$r) return $this->json(['error' => 'Recruteur non trouvé'], 404);

        $this->service->delete($r);
        return $this->json(['status' => 'deleted']);
    }
}