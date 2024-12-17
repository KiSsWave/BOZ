<?php

namespace backend\app\src\application\middleware;

use backend\app\src\core\services\auth\AuthzServiceInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;


class AuthzOrganisateurMiddleware
{
    private AuthzServiceInterface $authzService;

    public function __construct(AuthzServiceInterface $authzService)
    {
        $this->authzService = $authzService;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler) : Response
    {

        $user = $request->getAttribute('auth');
        $userid = $user->id;

        if (!$this->authzService->isAdmin($userid)) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Access forbidden']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);

    }

}