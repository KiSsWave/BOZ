<?php

namespace boz\application\action;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use boz\core\services\Blockchain\BlockServiceInterface;

class PayFactureAction extends AbstractAction
{
    private BlockServiceInterface $blockService;

    public function __construct(BlockServiceInterface $blockService)
    {
        $this->blockService = $blockService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $data = $rq->getParsedBody();
        $factureId = $data['facture_id'] ?? null;

        if (!$factureId) {
            $rs->getBody()->write(json_encode([
                'error' => "L'ID de la facture est requis"
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $user = $rq->getAttribute('user');
            if (!$user) {
                throw new Exception("Utilisateur non authentifié");
            }

            $this->blockService->payerFacture($factureId,$user->getID(),$user->getEmail());

            $rs->getBody()->write(json_encode([
                'message' => "La facture avec l'ID {$factureId} a été payée avec succès"
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (Exception $e) {
            $rs->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}