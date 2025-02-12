<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function( \Slim\App $app):\Slim\App {

    $app->add(boz\application\middleware\CorsMiddleware::class);

    $app->post('/signin', boz\application\action\SignInAction::class);
    $app->post('/register', boz\application\action\RegisterAction::class);


    $app->group('user', function () use ($app){

        $app->get('/balance', boz\application\action\GetBalanceAction::class);
        $app->get('/history', boz\application\action\GetHistoryAction::class);
        $app->post('/pay', boz\application\action\PayFactureAction::class);


    })->add(boz\application\middleware\AuthnMiddleware::class);
        //->add(boz\application\middleware\AuthzUserMiddleware::class);

    $app->group('vendeur', function () use ($app){
        $app->post('/facture', boz\application\action\CreateFactureAction::class);

    })->add(boz\application\middleware\AuthnMiddleware::class);
        //->add(boz\application\middleware\AuthzVendeurMiddleware::class);

    $app->group('admin', function () use ($app){

        $app->post('/give', boz\application\action\GiveCashAction::class);

    })->add(boz\application\middleware\AuthnMiddleware::class);
        //->add(boz\application\middleware\AuthzAdminMiddleware::class);



    return $app;
};