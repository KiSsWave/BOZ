<?php


namespace boz\application\action;

use boz\core\services\auth\AuthnServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use boz\core\services\user\UserServiceInterface;

class SearchUsersAction extends AbstractAction
{
    private AuthnServiceInterface $authnService;

    public function __construct(AuthnServiceInterface $userService)
    {
        $this->authnService = $userService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        try {
            $query = $rq->getQueryParams()['query'] ?? '';
            $currentUser = $rq->getAttribute('user');

            $users = $this->authnService->searchUsers($query, $currentUser->getLogin());

            $rs->getBody()->write(json_encode([
                'users' => $users
            ]));
            return $rs->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $rs->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $rs->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
}