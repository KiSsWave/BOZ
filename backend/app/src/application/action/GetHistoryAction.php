<?php

namespace boz\application\action;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use boz\core\services\Blockchain\BlockServiceInterface;

class GetHistoryAction extends AbstractAction
{
    private BlockServiceInterface $blockService;

    public function __construct(BlockServiceInterface $blockService)
    {
        $this->blockService = $blockService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $user = $rq->getAttribute('user');
        $userId = $user['id'];

        try {
            $history = $this->blockService->afficherHistorique($userId);


            $formattedHistory = array_map(function ($entry) {
                return [
                    'transaction_id' => $entry['transaction_id'],
                    'price' => $entry['price'],
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


