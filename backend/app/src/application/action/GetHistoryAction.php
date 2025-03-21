<?php

namespace boz\application\action;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use boz\core\services\Blockchain\AuthnServiceInterface;

class GetHistoryAction extends AbstractAction
{
    private AuthnServiceInterface $blockService;

    public function __construct(AuthnServiceInterface $blockService)
    {
        $this->blockService = $blockService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $user = $rq->getAttribute('user');
        $userId = $user->getId();

        try {
            $history = $this->blockService->afficherHistorique($userId);


            $formattedHistory = array_map(function ($entry) {
                return [
                    'transaction_id' => $entry['transaction_id'],
                    'amount' => $entry['amount'],
                    'type' => $entry['type'],
                    'timestamp' => $entry['block_timestamp']
                ];
            }, $history);

            $responseData = [
                'history' => $formattedHistory
            ];

            $rs->getBody()->write(json_encode($responseData, JSON_PRETTY_PRINT));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            $errorResponse = [
                'error' => $e->getMessage()
            ];
            $rs->getBody()->write(json_encode($errorResponse, JSON_PRETTY_PRINT));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }

}


