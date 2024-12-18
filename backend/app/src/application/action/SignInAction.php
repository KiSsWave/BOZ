<?php

namespace boz\application\action;

use boz\application\action\AbstractAction;
use boz\application\providers\AuthnProviderInterface;
use boz\core\dto\CredentialDTO;
use boz\core\services\auth\AuthServiceBadDataException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

class SignInAction extends AbstractAction
{
    private AuthnProviderInterface $authnProvider;

    public function __construct(AuthnProviderInterface $authnProvider){
        $this->authnProvider = $authnProvider;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $data = $rq->getParsedBody();

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;


        try {
            $user = $this->authnProvider->signin(new CredentialDTO($email, $password));
            $token = $user->getToken();
        }catch(AuthServiceBadDataException $e){
            throw new HttpBadRequestException($rq, $e->getMessage());
        }

        $rs->getBody()->write(json_encode([
            'token' => $token
        ]));

        return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}