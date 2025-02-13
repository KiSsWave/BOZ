<?php

namespace boz\application\middleware;

use boz\core\services\auth\AuthzServiceInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
class AuthzVendeurMiddleware
{
    private AuthzServiceInterface $authzService;

    public function __construct(AuthzServiceInterface $authzService)
    {
        $this->authzService = $authzService;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler) : Response
    {

        $user = $request->getAttribute('user');
        $userid = $user->ID;

        if (!$this->authzService->isVendeur($userid)) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Vous n\'avais pas les droits pour faire cette action']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);

    }
}