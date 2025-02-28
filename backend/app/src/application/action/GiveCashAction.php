<?php

namespace boz\application\action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use boz\core\services\Blockchain\AuthnServiceInterface;
use Slim\Exception\HttpBadRequestException;
use boz\core\dto\UserDTO;

class GiveCashAction extends AbstractAction
{
    private AuthnServiceInterface $blockService;

    public function __construct(AuthnServiceInterface $blockService)
    {
        $this->blockService = $blockService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $data = $rq->getParsedBody();
        $userLogin = $data['user_login'] ?? null;
        $amount = $data['amount'] ?? null;

        $admin = $rq->getAttribute('user');
        if (!$admin) {
            throw new HttpBadRequestException($rq, "Admin non authentifié");
        }

        $adminLogin = $admin->getEmail();

        try {
            if (!$userLogin || !$amount) {
                throw new HttpBadRequestException($rq, "Le login de l'utilisateur et le montant sont requis");
            }

            if (!is_numeric($amount) || $amount <= 0) {
                throw new HttpBadRequestException($rq, "Le montant doit être un nombre positif");
            }

            $this->blockService->giveCash($adminLogin, $userLogin, floatval($amount));

            $rs->getBody()->write(json_encode([
                'message' => "Transaction de {$amount} effectuée pour l'utilisateur {$userLogin}"
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            throw new HttpBadRequestException($rq, $e->getMessage());
        }
    }
}