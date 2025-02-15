<?php

namespace boz\Infrastructure\repositories;

use boz\core\domain\Blockchain\Block;
use boz\core\domain\Blockchain\Ticket;
use boz\core\domain\Blockchain\Transaction;
use boz\core\dto\TicketDTO;
use boz\core\repositoryInterfaces\TicketRepositoryInterface;
use Exception;
use PDO;
use PDOException;

class TicketRepository implements TicketRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }

    public function addTicket(TicketDTO $ticketDTO): void
    {
        try {
            $query = $this->pdo->prepare("
            INSERT INTO tickets(id, user_login, idadmin, message, type, status) 
            VALUES (:id, :userLogin, :idadmin, :message, :type, :status)
        ");
            $query->execute([
                ':id' => $ticketDTO->getId(),
                ':userLogin' => $ticketDTO->getUserLogin(),
                ':idadmin' => $ticketDTO->getAdminId(),
                ':message' => $ticketDTO->getMessage(),
                ':type' => $ticketDTO->getType(),
                ':status' => $ticketDTO->getStatus()
            ]);
        } catch (Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'ajout du ticket : ' . $e->getMessage());
        }
    }

    public function closeTicket(string $id): void
    {
        try {
            $query = $this->pdo->prepare("UPDATE tickets SET status = 'finish' WHERE id = :id");
            $query->execute([':id' => $id]);
        } catch (Exception $e) {
            throw new \RuntimeException('Erreur lors de la mise à jour du ticket : ' . $e->getMessage());
        }
    }

    public function takeTicket(string $ticketId, string $adminId): void
    {
        try {
            $query = $this->pdo->prepare("UPDATE tickets SET idadmin = :idadmin, status = 'en cours' WHERE id = :id");
            $query->execute([
                ':id' => $ticketId,
                ':idadmin' => $adminId
            ]);
        } catch (Exception $e) {
            throw new \RuntimeException('Erreur lors de la mise à jour du ticket : ' . $e->getMessage());
        }
    }

    public function getTicketsByAdminId(string $id): array
    {
        try {
            $query = $this->pdo->prepare("
            SELECT t.* 
            FROM tickets t 
            WHERE t.idadmin = :id
        ");
            $query->execute([':id' => $id]);
            $resultat = $query->fetchAll(PDO::FETCH_ASSOC);

            $tickets = [];
            foreach ($resultat as $row) {
                $ticket = new Ticket(
                    $row['user_login'],
                    $row['idadmin'],
                    $row['message'],
                    $row['type'],
                    $row['status']
                );
                $ticket->setId($row['id']);
                $tickets[] = $ticket;
            }
            return $tickets;
        } catch (Exception $e) {
            throw new \RuntimeException('Erreur lors de la recherche des tickets : ' . $e->getMessage());
        }
    }

    public function getTicketsPending(): array
    {
        try {
            $query = $this->pdo->prepare("
            SELECT t.*, t.user_login as user_login 
            FROM tickets t 
            WHERE t.status = 'en attente'
        ");
            $query->execute();
            $resultat = $query->fetchAll(PDO::FETCH_ASSOC);

            $tickets = [];
            foreach ($resultat as $row) {
                $ticket = new Ticket(
                    $row['user_login'],
                    $row['idadmin'],
                    $row['message'],
                    $row['type'],
                    $row['status']
                );
                $ticket->setId($row['id']);
                $tickets[] = $ticket;
            }
            return $tickets;
        } catch (Exception $e) {
            throw new \RuntimeException('Erreur lors de la recherche des tickets : ' . $e->getMessage());
        }
    }

    public function getTicketsByUserId(string $userLogin): array
    {
        try {
            $query = $this->pdo->prepare("
            SELECT t.* 
            FROM tickets t 
            WHERE t.user_login = :userLogin
        ");
            $query->execute([':userLogin' => $userLogin]);
            $resultat = $query->fetchAll(PDO::FETCH_ASSOC);

            $tickets = [];
            foreach ($resultat as $row) {
                $ticket = new Ticket(
                    $row['user_login'],
                    $row['idadmin'],
                    $row['message'],
                    $row['type'],
                    $row['status']
                );
                $ticket->setId($row['id']);
                $tickets[] = $ticket;
            }
            return $tickets;
        } catch (Exception $e) {
            throw new \RuntimeException('Erreur lors de la recherche des tickets : ' . $e->getMessage());
        }
    }
}
