<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function (\Slim\App $app): \Slim\App {

    $app->add(boz\application\middleware\CorsMiddleware::class);

    $app->post('/signin', boz\application\action\SignInAction::class);
    $app->post('/register', boz\application\action\RegisterAction::class);




    $app->get('/balance', boz\application\action\GetBalanceAction::class)->add(boz\application\middleware\AuthnMiddleware::class);
    $app->get('/history', boz\application\action\GetHistoryAction::class)->add(boz\application\middleware\AuthnMiddleware::class);


    $app->group('user', function () use ($app) {

        $app->post('/pay', boz\application\action\PayFactureAction::class)->add(boz\application\middleware\AuthzUserMiddleware::class);
        $app->post('/tickets', boz\application\action\AddTicketAction::class)->add(boz\application\middleware\AuthzUserMiddleware::class);
        $app->get('/ticket', boz\application\action\GetTicketByUserIdAction::class)->add(boz\application\middleware\AuthzUserMiddleware::class);


    })->add(boz\application\middleware\AuthnMiddleware::class);



    $app->group('vendeur', function () use ($app) {
        $app->post('/facture', boz\application\action\CreateFactureAction::class)->add(boz\application\middleware\AuthzVendeurMiddleware::class);

    })->add(boz\application\middleware\AuthnMiddleware::class);



    $app->group('admin', function () use ($app) {

        $app->post('/give', boz\application\action\GiveCashAction::class)->add(boz\application\middleware\AuthzAdminMiddleware::class);

        $app->get('/tickets/admin', boz\application\action\GetTicketByAdminIdAction::class)->add(boz\application\middleware\AuthzAdminMiddleware::class);
        $app->patch('/tickets/close/{ticketId}', boz\application\action\CloseTicketAction::class)->add(boz\application\middleware\AuthzAdminMiddleware::class);
        $app->patch('/tickets', boz\application\action\TakeTicketByAdminAction::class)->add(boz\application\middleware\AuthzAdminMiddleware::class);
    })->add(boz\application\middleware\AuthnMiddleware::class);





    return $app;
};


