<?php

namespace boz\application\action;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use boz\core\services\Blockchain\BlockServiceInterface;

class GetBalanceAction extends AbstractAction
{
    private BlockServiceInterface $blockService;

    public function __construct(BlockServiceInterface $blockService)
    {
        $this->blockService = $blockService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $user = $rq->getAttribute('user');
        $userId = $user->getId();

        try{
            $balance = $this->blockService->afficherSolde($userId);

            if(!$balance['success']){
                $array = [
                    'ERROR : ' => $balance
                ];
            }else{
                $array = [
                    'balance' => $balance['balance']
                ];
            }
            $rs->getBody()->write(json_encode($array));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);
        }catch(BlockchainCompromiseException $e){
            $rs->getBody()->write(json_encode([
                'error' => $e->getMessage(),
                'type' => 'blockchain_compromise'
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(503);
        } catch(Exception $e){
            $rs->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

    }
}

