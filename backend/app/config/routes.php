<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function (\Slim\App $app): \Slim\App {

    $app->add(boz\application\middleware\CorsMiddleware::class);

    $app->post('/signin', boz\application\action\SignInAction::class);
    $app->post('/register', boz\application\action\RegisterAction::class);



    $app->patch('/profile', boz\application\action\UpdateProfileAction::class)->add(boz\application\middleware\AuthnMiddleware::class);

    $app->get('/balance', boz\application\action\GetBalanceAction::class)->add(boz\application\middleware\AuthnMiddleware::class);
    $app->get('/history', boz\application\action\GetHistoryAction::class)->add(boz\application\middleware\AuthnMiddleware::class);

    $app->post('/tickets', boz\application\action\AddTicketAction::class)->add(boz\application\middleware\AuthnMiddleware::class);
    $app->get('/tickets', boz\application\action\GetTicketsByUserIdAction::class)->add(boz\application\middleware\AuthnMiddleware::class);


    $app->get('/conversations', boz\application\action\GetConversationsAction::class)->add(boz\application\middleware\AuthnMiddleware::class);
    $app->post('/conversations', boz\application\action\CreateConversationAction::class)->add(boz\application\middleware\AuthnMiddleware::class);
    $app->get('/conversations/{id}/messages', boz\application\action\GetMessagesAction::class)->add(boz\application\middleware\AuthnMiddleware::class);
    $app->post('/conversations/{id}/messages', boz\application\action\SendMessageAction::class)->add(boz\application\middleware\AuthnMiddleware::class);



    $app->group('user', function () use ($app) {

        $app->post('/pay', boz\application\action\PayFactureAction::class)->add(boz\application\middleware\AuthzUserMiddleware::class);
        $app->patch('/role', boz\application\action\ChangeRoleAction::class)->add(boz\application\middleware\AuthzUserMiddleware::class);
        $app->get('/users/search', boz\application\action\SearchUsersAction::class)->add(boz\application\middleware\AuthzUserMiddleware::class);

    })->add(boz\application\middleware\AuthnMiddleware::class);



    $app->group('vendeur', function () use ($app) {

        $app->post('/facture', boz\application\action\CreateFactureAction::class)->add(boz\application\middleware\AuthzVendeurMiddleware::class);
        $app->get('/factures', boz\application\action\GetFacturesByUserLoginAction::class)->add(boz\application\middleware\AuthzVendeurMiddleware::class);
        $app->get('/facture/{factureId}', boz\application\action\GetFactureByIdAction::class)->add(boz\application\middleware\AuthzVendeurMiddleware::class);

    })->add(boz\application\middleware\AuthnMiddleware::class);



    $app->group('admin', function () use ($app) {

        $app->post('/give', boz\application\action\GiveCashAction::class)->add(boz\application\middleware\AuthzAdminMiddleware::class);

        $app->get('/tickets/pending', boz\application\action\GetTicketsPendingAction::class)->add(boz\application\middleware\AuthzAdminMiddleware::class);

        $app->get('/tickets/admin', boz\application\action\GetTicketsByAdminIdAction::class)->add(boz\application\middleware\AuthzAdminMiddleware::class);
        $app->patch('/tickets/close/{ticketId}', boz\application\action\CloseTicketAction::class)->add(boz\application\middleware\AuthzAdminMiddleware::class);
        $app->patch('/tickets', boz\application\action\TakeTicketByAdminAction::class)->add(boz\application\middleware\AuthzAdminMiddleware::class);


        $app->post('/tickets/start-conversation', boz\application\action\StartConversationFromTicketAction::class)->add(boz\application\middleware\AuthzAdminMiddleware::class);
    })->add(boz\application\middleware\AuthnMiddleware::class);





    return $app;
};