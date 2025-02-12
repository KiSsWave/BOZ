<?php


namespace boz\application\action;


use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use boz\core\services\Blockchain\BlockServiceInterface;


class CreateFactureAction extends AbstractAction
{
    private BlockServiceInterface $blockService;

    public function __construct(BlockServiceInterface $blockService)
    {
        $this->blockService = $blockService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $data = $rq->getParsedBody();

        $user = $rq->getAttribute('user');
        $userId = $user->getId();
        $tarif = $data['tarif'] ?? null;
        $label = $data['label'] ?? null;
        try{
            $this->blockService->creerFacture($userId,$tarif, $label);
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);
        }catch(Exception $e){
            $rs->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }
}

