<?php

namespace boz\application\middleware;

use boz\core\services\auth\AuthzServiceInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthzAdminMiddleware
{
    private AuthzServiceInterface $authzService;

    public function __construct(AuthzServiceInterface $authzService)
    {
        $this->authzService = $authzService;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler) : Response
    {
        $user = $request->getAttribute('user');

        if (!$user) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'User not authenticated']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $userId = $user->getId();

        if (!$this->authzService->isAdmin($userId)) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Access forbidden - Admin rights required']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}