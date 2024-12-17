<?php

namespace backend\app\src\application\action;

use backend\app\src\application\action\AbstractAction;
use backend\app\src\application\providers\AuthnProviderInterface;
use backend\app\src\core\dto\CredentialDTO;
use backend\app\src\core\services\auth\AuthServiceBadDataException;
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