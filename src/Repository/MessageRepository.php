<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @return Message[] Returns messages sent by the user
     */
    public function findSentMessages(int $userId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.emetteur_Message = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.dateEnvoi_Message', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Message[] Returns messages received by the user
     */
    public function findReceivedMessages(int $userId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.destinataire_Message = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.dateEnvoi_Message', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Message[] Returns conversation between two users (both directions)
     */
    public function findConversation(int $userId, int $correspondantId): array
    {
        return $this->createQueryBuilder('m')
            ->where(
                '(m.emetteur_Message = :userId AND m.destinataire_Message = :correspondantId) OR
                 (m.emetteur_Message = :correspondantId AND m.destinataire_Message = :userId)'
            )
            ->setParameter('userId', $userId)
            ->setParameter('correspondantId', $correspondantId)
            ->orderBy('m.dateEnvoi_Message', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Utilisateur[] Returns unique correspondents (both sent and received messages)
     */
    public function findCorrespondents(int $userId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT DISTINCT u.id, u.type, u.nom_Utilisateur, u.prenom_Utilisateur, u.email_Utilisateur
            FROM message m
            INNER JOIN utilisateur u ON (m.destinataire_message_id = u.id OR m.emetteur_message_id = u.id)
            WHERE (m.emetteur_message_id = :userId OR m.destinataire_message_id = :userId)
            AND u.id != :userId
            GROUP BY u.id
            ORDER BY MAX(m.date_envoi_message) DESC
        ';
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $userId);
        $result = $stmt->executeQuery();
        
        $userIds = array_map(fn($row) => $row['id'], $result->fetchAllAssociative());
        
        if (empty($userIds)) {
            return [];
        }
        
        return $this->getEntityManager()->getRepository('App\Entity\Utilisateur')->findBy(['id' => $userIds]);
    }
}
