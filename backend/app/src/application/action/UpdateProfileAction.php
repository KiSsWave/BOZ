<?php

namespace boz\application\action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use boz\core\services\auth\AuthnServiceInterface;
use boz\core\dto\CredentialDTO;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class UpdateProfileAction extends AbstractAction
{
    private AuthnServiceInterface $authnService;

    public function __construct(AuthnServiceInterface $authnService)
    {
        $this->authnService = $authnService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {

        $user = $rq->getAttribute('user');
        if (!$user) {
            throw new HttpUnauthorizedException($rq, "Utilisateur non authentifié");
        }


        $data = $rq->getParsedBody();
        $login = $data['login'] ?? null;
        $email = $data['email'] ?? null;
        $currentPassword = $data['current_password'] ?? null;
        $newPassword = $data['new_password'] ?? null;

        try {

            if (!$login || !$email) {
                throw new HttpBadRequestException($rq, "Le login et l'email sont requis");
            }


            if ($newPassword) {
                if (!$currentPassword) {
                    throw new HttpBadRequestException($rq, "Le mot de passe actuel est requis pour le changer");
                }
                $credentialDTO = new CredentialDTO($user->getLogin(), $currentPassword);
                $this->authnService->verifyPassword($credentialDTO);
            }

            $this->authnService->updateProfile($user->getID(), $login, $email, $newPassword);


            $rs->getBody()->write(json_encode([
                'message' => 'Profil mis à jour avec succès'
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            throw new HttpBadRequestException($rq, $e->getMessage());
        }
    }
}
