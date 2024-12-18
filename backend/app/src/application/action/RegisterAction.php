<?php

namespace boz\application\action;

use boz\application\providers\AuthnProviderInterface;
use boz\core\dto\CredentialDTO;
use boz\core\services\auth\AuthServiceBadDataException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class RegisterAction extends AbstractAction
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
        $login = $data['login'] ?? null;
        $role = $data['role'];

        try{
            $this->authnProvider->register(new CredentialDTO($email, $password), $login, $role);

        }catch(AuthServiceBadDataException $e){
            throw new HttpBadRequestException($rq, $e->getMessage());
        }

        return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);

    }
}