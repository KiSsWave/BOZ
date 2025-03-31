<?php

namespace boz\Infrastructure\repositories;

use boz\core\domain\Blockchain\Block;
use boz\core\repositoryInterfaces\BlockRepositoryInterface;
use boz\core\repositoryInterfaces\RepositoryEntityNotFoundException;
use PDO;
use Ramsey\Uuid\Uuid;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Exception;

class BlockRepository implements BlockRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getBalanceByUserId(string $userId): array
    {
        try {
            $verificationResult = $this->verifyBlockchain();
        

        if (!$verificationResult['valid']) {
            return [
                'success' => false,
                'balance' => null,
                'message' => 'Le système de paiement est temporairement indisponible pour des raisons de corruption de la blockchain. Veuillez contacter un administrateur.'
            ];
        }
            $loginStmt = $this->pdo->prepare("SELECT login FROM users WHERE id = :user_id");
            $loginStmt->execute(['user_id' => $userId]);
            $login = $loginStmt->fetchColumn();
            
            if (!$login) {
                return [
                    'success' => false,
                    'balance' => null,
                    'message' => 'Utilisateur non trouvé.'
                ];           
             }
            
            $stmt = $this->pdo->prepare("
            SELECT 
                COALESCE(
                    (SELECT COALESCE(SUM(amount), 0) FROM blocks WHERE receiver = :login) -
                    (SELECT COALESCE(SUM(amount), 0) FROM blocks WHERE emitter = :login AND emitter != receiver),
                    0
                ) AS balance
            ");
            $stmt->execute(['login' => $login]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $balance = floatval($result['balance'] ?? 0.0);
            return [
                'success' => true,
                'balance' => $balance,
                'message' => 'Solde récupéré avec succès.'
            ];        
        } catch (\Exception $e) {
            return [
                'success' => false,
                'balance' => null,
                'message' => 'Une erreur est survenue lors de la consultation de votre solde. Veuillez réessayer ultérieurement.'
            ];        }
    }

    public function getHistoryByUserId(string $userId): array
    {
        try {
            $loginStmt = $this->pdo->prepare("SELECT login FROM users WHERE id = :user_id");
            $loginStmt->execute(['user_id' => $userId]);
            $login = $loginStmt->fetchColumn();
            
            if (!$login) {
                throw new RepositoryEntityNotFoundException("Utilisateur avec ID {$userId} non trouvé.");
            }
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    id,
                    hash,
                    previous_hash,
                    timestamp,
                    account,
                    amount,
                    CASE 
                        WHEN emitter = receiver THEN 'add'
                        WHEN receiver = :login THEN 'add'
                        WHEN emitter = :login THEN 'pay'
                    END AS transaction_type,
                    emitter,
                    receiver
                FROM blocks
                WHERE emitter = :login OR receiver = :login
                ORDER BY timestamp DESC
            ");

            $stmt->execute(['login' => $login]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors de la récupération de l'historique : " . $e->getMessage());
        }
    }

    public function getLoginByUserId(string $userId): string
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT login
                FROM users
                WHERE id = :user_id
            ");

            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new RepositoryEntityNotFoundException("Utilisateur non trouvé.");
            }

            return $result['login'];
        } catch (\PDOException $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors de la récupération du login : " . $e->getMessage());
        }
    }

    public function createFacture($login, float $tarif, string $label, ?string $buyerLogin = null): void
    {
        try {
            $factureId = Uuid::uuid4()->toString();

            $qrCodeData = [
                'id' => $factureId,
                'tarif' => $tarif,
                'label' => $label,
                'seller' => $login
            ];
            
            if ($buyerLogin) {
                $qrCodeData['buyer'] = $buyerLogin;
            }

            $qrCodeJson = json_encode($qrCodeData);

            $qrCode = new QrCode($qrCodeJson);
            $writer = new PngWriter();
            $result = $writer->writeString($qrCode);

            $query = "
            INSERT INTO facture (id, seller_login, qr_code, label, amount, status, created_at";
            
            $params = [
                'id' => $factureId,
                'seller_login' => $login,
                'qr_code' => $result,
                'label' => $label,
                'amount' => $tarif,
                'status' => 'non payée'
            ];
            
            if ($buyerLogin) {
                $query .= ", buyer_login) VALUES (:id, :seller_login, :qr_code, :label, :amount, :status, CURRENT_TIMESTAMP, :buyer_login)";
                $params['buyer_login'] = $buyerLogin;
            } else {
                $query .= ") VALUES (:id, :seller_login, :qr_code, :label, :amount, :status, CURRENT_TIMESTAMP)";
            }
            
            $stmt = $this->pdo->prepare($query);
            
            foreach ($params as $key => $value) {
                $paramType = ($key === 'qr_code') ? PDO::PARAM_LOB : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }

            $stmt->execute();
        } catch (Exception $e) {
            error_log("Erreur lors de la création de la facture : " . $e->getMessage());
            throw new RepositoryEntityNotFoundException("Erreur lors de la création de la facture : " . $e->getMessage());
        }
    }

    public function payFacture(string $factureId, string $userId, string $userLogin, string $role): void
    {
        try {
            $stmt = $this->pdo->prepare("SELECT seller_login, buyer_login, amount, status FROM facture WHERE id = :id");
            $stmt->execute(['id' => $factureId]);
            $facture = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$facture) {
                throw new RepositoryEntityNotFoundException("La facture avec l'ID {$factureId} n'existe pas.");
            }

            if ($facture['status'] === 'payée') {
                throw new RepositoryEntityNotFoundException("La facture avec l'ID {$factureId} est déjà payée.");
            }
            
            if ($facture['buyer_login'] !== null && $facture['buyer_login'] !== $userLogin) {
                throw new RepositoryEntityNotFoundException("Cette facture est réservée à un acheteur spécifique.");
            }

            $sellerLogin = $facture['seller_login'];
            $amount = (float) $facture['amount'];

            if (!$this->isTransactionValid($userId, $amount, $role)) {
                throw new Exception("Solde insuffisant pour effectuer ce paiement.");
            }

            $this->pdo->beginTransaction();
            
            try {
                $this->addBlock($userLogin, $amount, $userLogin, $sellerLogin,$role);
                
                $updateQuery = "UPDATE facture SET status = :status";
                $params = [
                    'status' => 'payée',
                    'id' => $factureId
                ];
                
                if ($facture['buyer_login'] === null) {
                    $updateQuery .= ", buyer_login = :buyer_login";
                    $params['buyer_login'] = $userLogin;
                }
                
                $updateQuery .= " WHERE id = :id";
                
                $this->pdo->prepare($updateQuery)->execute($params);
                
                $this->pdo->commit();
            } catch (Exception $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                throw $e;
            }
        } catch (Exception $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors du paiement de la facture : " . $e->getMessage());
        }
    }

    public function getLastBlock(): array
    {
        try {
            $stmt = $this->pdo->query("
            SELECT * FROM blocks
            ORDER BY timestamp DESC
            LIMIT 1
        ");
            $block = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$block) {
                throw new RepositoryEntityNotFoundException("Aucun bloc trouvé.");
            }

            return $block;
        } catch (Exception $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors de la récupération du dernier bloc : " . $e->getMessage());
        }
    }

    public function addBlock(string $accountLogin, float $amount, string $emitter, string $receiver, string $role): void
    {
        try {
            $userIdStmt = $this->pdo->prepare("SELECT id FROM users WHERE login = :login");
            $userIdStmt->execute(['login' => $accountLogin]);
            $userId = $userIdStmt->fetchColumn();
            
            if (!$userId) {
                throw new Exception("Utilisateur avec login {$accountLogin} non trouvé.");
            }
            
            if ($emitter === $accountLogin && $emitter !== $receiver) {
                if (!$this->isTransactionValid($userId, $amount, $role)) {
                    throw new Exception("Transaction invalide pour l'utilisateur avec login {$accountLogin}.");
                }
            }

            $startedTransaction = false;
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $startedTransaction = true;
            }

            $lastBlock = null;
            try {
                $lastBlock = $this->getLastBlock();
            } catch (RepositoryEntityNotFoundException $e) {
            }

            $blockId = Uuid::uuid4()->toString();
            $previousHash = $lastBlock ? $lastBlock['hash'] : '0';
            $blockHash = hash('sha256', $blockId . $accountLogin . $amount . $emitter . $receiver . time());

            $newBlockStmt = $this->pdo->prepare("
                INSERT INTO blocks (id, hash, previous_hash, account, amount, emitter, receiver, timestamp)
                VALUES (:id, :hash, :previous_hash, :account, :amount, :emitter, :receiver, NOW())
            ");

            $newBlockStmt->execute([
                'id' => $blockId,
                'hash' => $blockHash,
                'previous_hash' => $previousHash,
                'account' => $accountLogin,
                'amount' => $amount,
                'emitter' => $emitter,
                'receiver' => $receiver
            ]);

            if ($startedTransaction) {
                $this->pdo->commit();
            }
        } catch (Exception $e) {
            if (isset($startedTransaction) && $startedTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new Exception("Erreur lors de l'ajout d'un nouveau bloc : " . $e->getMessage());
        }
    }

    public function isTransactionValid(string $userId, float $amount, string $role): bool
    {
        if ($role == 3){
            return true;
        }
        $balance = $this->getBalanceByUserId($userId);
        if ($balance < $amount) {
            return false;
        }
        return true;
    }

    public function getFactureById(string $factureId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT *
                FROM facture
                WHERE id = :id
            ");

            $stmt->execute(['id' => $factureId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new RepositoryEntityNotFoundException("Facture non trouvée.");
            }

            return $result;
        } catch (\PDOException $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors de la récupération de la facture : " . $e->getMessage());
        }
    }

    public function getFacturesByUserLogin(string $userLogin): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT *
                FROM facture
                WHERE seller_login = :seller_login
            ");

            $stmt->execute(['seller_login' => $userLogin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors de la récupération des factures : " . $e->getMessage());
        }
    }

    public function getFacturesByBuyerLogin(string $buyerLogin): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT *
                FROM facture
                WHERE buyer_login = :buyer_login
            ");

            $stmt->execute(['buyer_login' => $buyerLogin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors de la récupération des factures : " . $e->getMessage());
        }
    }

    

    public function createGenesisBlock(string $adminLogin): void
    {
        try {
            $this->pdo->beginTransaction();

            $blockId = Uuid::uuid4()->toString();
            $blockHash = hash('sha256', $blockId . $adminLogin . '0' . 'genesis' . time());

            $stmt = $this->pdo->prepare("
                INSERT INTO blocks (id, hash, previous_hash, account, amount, emitter, receiver, timestamp)
                VALUES (:id, :hash, :previous_hash, :account, :amount, :emitter, :receiver, NOW())
            ");

            $stmt->execute([
                'id' => $blockId,
                'hash' => $blockHash,
                'previous_hash' => '0',
                'account' => $adminLogin,
                'amount' => 0.0,
                'emitter' => $adminLogin,
                'receiver' => $adminLogin
            ]);

            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new Exception("Erreur lors de la création du bloc de genèse : " . $e->getMessage());
        }
    }

    public function verifyBlockchain(): array
    {
        try {
            // Récupérer tous les blocs ordonnés par timestamp
            $stmt = $this->pdo->prepare("
            SELECT id, hash, previous_hash, timestamp, account, amount, emetteur, recepteur
            FROM blocks
            ORDER BY timestamp ASC
        ");
            $stmt->execute();
            $blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si la chaîne est vide, elle est considérée comme valide
            if (empty($blocks)) {
                return [
                    'valid' => true,
                    'message' => 'La blockchain est vide mais valide.'
                ];
            }

            $invalidBlocks = [];

            // Vérifier chaque bloc individuellement et la liaison avec le bloc précédent
            for ($i = 0; $i < count($blocks); $i++) {
                $currentBlock = $blocks[$i];

                // Recalculer le hash du bloc actuel pour vérifier s'il a été modifié
                $calculatedHash = hash('sha256',
                    $currentBlock['id'] .
                    $currentBlock['emetteur'] .
                    $currentBlock['recepteur'] .
                    $currentBlock['amount'] .
                    strtotime($currentBlock['timestamp'])
                );

                // Vérifier si le hash stocké correspond au hash calculé
                if ($calculatedHash !== $currentBlock['hash']) {
                    $invalidBlocks[] = [
                        'block_id' => $currentBlock['id'],
                        'error' => 'Hash invalide',
                        'stored_hash' => $currentBlock['hash'],
                        'calculated_hash' => $calculatedHash
                    ];
                }

                // Pour tous les blocs sauf le premier, vérifier la liaison avec le bloc précédent
                if ($i > 0) {
                    $previousBlock = $blocks[$i - 1];

                    // Vérifier si le previous_hash pointe correctement vers le hash du bloc précédent
                    if ($currentBlock['previous_hash'] !== $previousBlock['hash']) {
                        $invalidBlocks[] = [
                            'block_id' => $currentBlock['id'],
                            'error' => 'Chaînage invalide',
                            'stored_previous_hash' => $currentBlock['previous_hash'],
                            'actual_previous_hash' => $previousBlock['hash']
                        ];
                    }
                } else {
                    // Pour le premier bloc, vérifier que previous_hash est bien NULL
                    if ($currentBlock['previous_hash'] !== null) {
                        $invalidBlocks[] = [
                            'block_id' => $currentBlock['id'],
                            'error' => 'Le premier bloc ne devrait pas avoir de previous_hash',
                            'stored_previous_hash' => $currentBlock['previous_hash']
                        ];
                    }
                }
            }

            // Retourner le résultat de la vérification
            if (empty($invalidBlocks)) {
                return [
                    'valid' => true,
                    'message' => 'La blockchain est valide. Tous les blocs sont intègres.',
                    'block_count' => count($blocks)
                ];
            } else {
                return [
                    'valid' => false,
                    'message' => 'La blockchain est invalide. Des blocs ont été altérés.',
                    'invalid_blocks' => $invalidBlocks,
                    'block_count' => count($blocks)
                ];
            }

        } catch (Exception $e) {
            return [
                'valid' => false,
                'message' => 'Erreur lors de la vérification de la blockchain: ' . $e->getMessage()
            ];
        }
    }



}
