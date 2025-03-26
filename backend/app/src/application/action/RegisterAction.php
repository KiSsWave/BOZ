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


        if (!$this->isValidEmail($email)) {
            throw new HttpBadRequestException($rq, "Adresse email invalide.");
        }


        if (!$this->isValidPassword($password)) {
            throw new HttpBadRequestException($rq, "Le mot de passe doit contenir au moins 8 caractères, incluant une lettre majuscule, une lettre minuscule, un chiffre, et un caractère spécial.");
        }

        try {
            $this->authnProvider->register(new CredentialDTO($email, $password), $login, 1);
        } catch (AuthServiceBadDataException $e) {
            throw new HttpBadRequestException($rq, $e->getMessage());
        }

        return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    private function isValidEmail(?string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }


    private function isValidPassword(?string $password): bool
    {
        if (is_null($password) || strlen($password) < 8) {
            return false;
        }

        return preg_match('/[A-Z]/', $password) &&
            preg_match('/[a-z]/', $password) &&
            preg_match('/[0-9]/', $password) &&
            preg_match('/[\W_]/', $password);
    }
}
