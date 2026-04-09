<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Repository\UtilisateurRepository;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/users/messages')]
final class MessageController extends AbstractController
{
    public function __construct(
        private MessageRepository $messageRepo,
        private UtilisateurRepository $utilisateurRepo,
        private AuthService $authService,
        private EntityManagerInterface $em
    ) {}

    #[Route('/correspondants', name: 'message_correspondents', methods: ['GET'])]
    public function correspondents(Request $request): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $userId = (int) ($response['userId'] ?? null);

        $correspondents = $this->messageRepo->findCorrespondents($userId);
        $data = array_map([$this, 'mapCorrespondentToArray'], $correspondents);

        return $this->json($data);
    }

    #[Route('/sent/{id}', name: 'message_sent_show', methods: ['GET'])]
    public function sentShow(Request $request, int $id): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $userId = (int) ($response['userId'] ?? null);

        $message = $this->messageRepo->find($id);
        if (!$message) {
            return $this->json(['error' => 'Message non trouvé'], 404);
        }

        if ($message->getEmetteurMessage()->getId() !== $userId) {
            return $this->json(['error' => 'Accès refusé : ce message ne vous appartient pas'], 403);
        }

        return $this->json($this->mapMessageToArray($message));
    }

    #[Route('/received/{id}', name: 'message_received_show', methods: ['GET'])]
    public function receivedShow(Request $request, int $id): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $userId = (int) ($response['userId'] ?? null);

        $message = $this->messageRepo->find($id);
        if (!$message) {
            return $this->json(['error' => 'Message non trouvé'], 404);
        }

        if ($message->getDestinataireMessage()->getId() !== $userId) {
            return $this->json(['error' => 'Accès refusé : ce message ne vous est pas destiné'], 403);
        }

        return $this->json($this->mapMessageToArray($message));
    }

    #[Route('/send/{destinataireId}', name: 'message_send', methods: ['POST'])]
    public function send(Request $request, int $destinataireId): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $userId = (int) ($response['userId'] ?? null);

        // Vérifier que l'utilisateur n'envoie pas à lui-même
        if ($userId === $destinataireId) {
            return $this->json(['error' => 'Vous ne pouvez pas envoyer un message à vous-même'], 400);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Payload invalide'], 400);
        }

        if (empty($data['contenu'])) {
            return $this->json(['error' => 'Le champ "contenu" est requis'], 400);
        }

        $emetteur = $this->utilisateurRepo->find($userId);
        if (!$emetteur) {
            return $this->json(['error' => 'Utilisateur emetteur non trouvé'], 404);
        }

        $destinataire = $this->utilisateurRepo->find($destinataireId);
        if (!$destinataire) {
            return $this->json(['error' => 'Utilisateur destinataire non trouvé'], 404);
        }

        $message = new Message();
        $message->setContenuMessage($data['contenu']);
        $message->setDateEnvoiMessage(new \DateTimeImmutable());
        $message->setEmetteurMessage($emetteur);
        $message->setDestinataireMessage($destinataire);

        $this->em->persist($message);
        $this->em->flush();

        return $this->json($this->mapMessageToArray($message), 201);
    }

    #[Route('/{correspondantId}', name: 'message_conversation', methods: ['GET'])]
    public function conversation(Request $request, int $correspondantId): JsonResponse
    {
        $response = $this->authService->getConnectedUser($request);
        if (isset($response['error'])) {
            return new JsonResponse(['error' => $response['error']], 401);
        }
        $userId = (int) ($response['userId'] ?? null);

        // Vérifier que l'utilisateur n'accède pas à sa propre conversation
        if ($userId === $correspondantId) {
            return $this->json(['error' => 'Vous ne pouvez pas consulter une conversation avec vous-même'], 400);
        }

        $correspondant = $this->utilisateurRepo->find($correspondantId);
        if (!$correspondant) {
            return $this->json(['error' => 'Utilisateur correspondant non trouvé'], 404);
        }

        $messages = $this->messageRepo->findConversation($userId, $correspondantId);
        $data = array_map([$this, 'mapMessageToArray'], $messages);

        return $this->json([
            'correspondant' => [
                'id' => $correspondant->getId(),
                'nom' => $correspondant->getNomUtilisateur(),
                'prenom' => $correspondant->getPrenomUtilisateur(),
                'email' => $correspondant->getEmailUtilisateur(),
            ],
            'messages' => $data,
        ]);
    }

    private function mapMessageToArray(Message $message): array
    {
        return [
            'id' => $message->getId(),
            'contenu' => $message->getContenuMessage(),
            'dateEnvoi' => $message->getDateEnvoiMessage()?->format('Y-m-d H:i:s'),
            'emetteur' => [
                'id' => $message->getEmetteurMessage()->getId(),
                'nom' => $message->getEmetteurMessage()->getNomUtilisateur(),
                'prenom' => $message->getEmetteurMessage()->getPrenomUtilisateur(),
            ],
            'destinataire' => [
                'id' => $message->getDestinataireMessage()->getId(),
                'nom' => $message->getDestinataireMessage()->getNomUtilisateur(),
                'prenom' => $message->getDestinataireMessage()->getPrenomUtilisateur(),
            ],
        ];
    }

    private function mapCorrespondentToArray($correspondent): array
    {
        return [
            'id' => $correspondent->getId(),
            'nom' => $correspondent->getNomUtilisateur(),
            'prenom' => $correspondent->getPrenomUtilisateur(),
            'email' => $correspondent->getEmailUtilisateur(),
        ];
    }
}
