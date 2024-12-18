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
            try{
                $token = $rq->getHeader('Authorization')[0];
                $tokenstring = sscanf($token, 'Bearer %s')[0];
            }catch (\Exception $e){
                return (new Response())->withStatus(401);
            }

            $userDTO = $this->authProvider->getSignedInUser($tokenstring);
        } catch (ExpiredException|\UnexpectedValueException|BeforeValidException|SignatureInvalidException $e) {
            return (new Response())->withStatus(401);
        }

        $rq = $rq->withAttribute('auth', $userDTO);

        return $handler->handle($rq);
    }
}