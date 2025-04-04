<?php

namespace boz\application\action;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use boz\core\services\Blockchain\BlockServiceInterface;

class GetFacturesByBuyerLoginAction extends AbstractAction {
    private BlockServiceInterface $blockService;

    public function __construct(BlockServiceInterface $blockService) {
        $this->blockService = $blockService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        try {
            $user = $rq->getAttribute('user');
            $userLogin = $user->getEmail();

            $factures = $this->blockService->getFacturesByBuyerLogin($userLogin);


            $formattedFactures = array_map(function($facture) {
                if (isset($facture['qr_code'])) {

                    if (is_resource($facture['qr_code'])) {
                        $qrCodeContent = stream_get_contents($facture['qr_code']);
                        $facture['qr_code'] = base64_encode($qrCodeContent);
                    } else {

                        $facture['qr_code'] = base64_encode($facture['qr_code']);
                    }
                }
                return $facture;
            }, $factures);

            $jsonData = json_encode([
                'factures' => $formattedFactures
            ]);

            if ($jsonData === false) {
                throw new Exception('Erreur lors de l\'encodage JSON des factures');
            }

            $rs->getBody()->write($jsonData);
            return $rs
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch(Exception $e) {
            $jsonError = json_encode([
                'error' => $e->getMessage()
            ]);

            $rs->getBody()->write($jsonError);
            return $rs
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
    }
}