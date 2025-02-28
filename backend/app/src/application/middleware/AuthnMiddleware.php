<?php

namespace boz\application\middleware;

use boz\application\providers\AuthnProviderInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthnMiddleware
{
    private AuthnProviderInterface $authProvider;

    public function __construct(AuthnProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(Request $rq, RequestHandlerInterface $handler) : Response
    {
        try {

            if (!$rq->hasHeader('Authorization')) {
                throw new \Exception('Authorization header missing');
            }


            $token = $rq->getHeader('Authorization')[0];
            $tokenstring = sscanf($token, 'Bearer %s')[0];

            if (!$tokenstring) {
                throw new \Exception('Invalid token format');
            }


            $userDTO = $this->authProvider->getSignedInUser($tokenstring);

            // Vérifier que userDTO n'est pas null
            if (!$userDTO) {
                throw new \Exception('Invalid user data');
            }


            $rq = $rq->withAttribute('user', $userDTO);

            return $handler->handle($rq);

        } catch (ExpiredException $e) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Token expired']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'User not authenticated']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }
}