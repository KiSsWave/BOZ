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

        $data = $rq->getParsedBody();;
        $tarif = $data['tarif'] ?? null;
        $label = $data['label'] ?? null;

        $vendeur = $rq->getAttribute('user');
        if (!$vendeur) {
            throw new HttpBadRequestException($rq, "Vendeur non authentifié");
        }
        $vendeurLogin = $vendeur->getEmail();


        try{
            $this->blockService->creerFacture($vendeurLogin,$tarif, $label);
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);
        }catch(Exception $e){
            $rs->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }
}
