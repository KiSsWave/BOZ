<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function( \Slim\App $app):\Slim\App {

    $app->add(boz\application\middleware\CorsMiddleware::class);

    $app->post('/signin', boz\application\action\SignInAction::class);
    $app->post('/register', boz\application\action\RegisterAction::class);
    $app->post('/pay/{id}', boz\application\action\PayFactureAction::class);
    $app->post('/tickets', \boz\application\action\AddTicketAction::class);
    $app->patch('/tickets', \boz\application\action\TakeTicketByAdminAction::class);
    $app->get('/tickets/{adminId}', \boz\application\action\GetTicketByAdminIdAction::class);
    $app->get('/ticket/{userId}', \boz\application\action\GetTicketByUserIdAction::class);
    $app->patch('/tickets/close/{ticketId}', \boz\application\action\CloseGameAction::class);


    $app->group('user', function () use ($app) {

        $app->get('/balance', boz\application\action\GetBalanceAction::class)->add(boz\application\middleware\AuthnMiddleware::class);
        $app->get('/history', boz\application\action\GetHistoryAction::class)->add(boz\application\middleware\AuthnMiddleware::class);

    })->add(boz\application\middleware\AuthnMiddleware::class);

    $app->group('vendeur', function () use ($app) {

        $app->post('/facture', boz\application\action\CreateFactureAction::class)->add(boz\application\middleware\AuthnMiddleware::class);

    })->add(boz\application\middleware\AuthnMiddleware::class)
        ->add(boz\application\middleware\AuthzVendeurMiddleware::class);


    return $app;
};

