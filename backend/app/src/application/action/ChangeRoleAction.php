<?php

namespace boz\application\action;


use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use boz\core\services\auth\AuthnServiceInterface;


class ChangeRoleAction extends AbstractAction {

    private AuthnServiceInterface $userService;

    public function __construct(AuthnServiceInterface $userService) {
        $this->userService = $userService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {

        $user = $rq->getAttribute('user');
        $userId = $user -> getID();

        try {
            $this->userService->changeRole($userId, 2);
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            $rs->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }
}
