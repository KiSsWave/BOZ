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
            $query = $this->pdo->prepare("INSERT INTO tickets(id, iduser, idadmin, message, type, status) VALUES (:id, :iduser, :idadmin, :message, :type, :status)");
            $query->execute([
                ':id' => $ticketDTO->getId(),
                ':iduser' => $ticketDTO->getUserId(),
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
            $query = $this->pdo->prepare("UPDATE tickets SET idadmin = :idadmin WHERE id = :id");
            $query->execute([
                ':id' => $ticketId,
                ':idadmin' => $adminId,
            ]);
        } catch (Exception $e) {
            throw new \RuntimeException('Erreur lors de la mise à jour du ticket : ' . $e->getMessage());
        }
    }

    public function getTicketsByAdminId(string $id): array
    {
        try {
            $query = $this->pdo->prepare("SELECT * FROM tickets WHERE idadmin = :id");
            $query->execute([':id' => $id]);
            $resultat = $query->fetchAll(PDO::FETCH_ASSOC);

            $tickets = [];
            foreach ($resultat as $row) {
                $ticket = new Ticket(
                    $row['iduser'],
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
            $query = $this->pdo->prepare("SELECT * FROM tickets WHERE status = 'en attente'");
            $query->execute();
            $resultat = $query->fetchAll(PDO::FETCH_ASSOC);

            $tickets = [];
            foreach ($resultat as $row) {
                $ticket = new Ticket(
                    $row['iduser'],
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

    public function getTicketsByUserId(string $id): array
    {
        try {
            $query = $this->pdo->prepare("SELECT * FROM tickets WHERE iduser = :id");
            $query->execute([':id' => $id]);
            $resultat = $query->fetchAll(PDO::FETCH_ASSOC);

            $tickets = [];
            foreach ($resultat as $row) {
                $ticket = new Ticket(
                    $row['iduser'],
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
