<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function( \Slim\App $app):\Slim\App {

    $app->add(boz\application\middleware\CorsMiddleware::class);

    $app->post('/signin', boz\application\action\SignInAction::class);
    $app->post('/register', boz\application\action\RegisterAction::class);
    $app->get('/balance', boz\application\action\GetBalanceAction::class);
    $app->get('/history', boz\application\action\GetHistoryAction::class);

    $app->group('', function () use ($app){

    })->add(boz\application\middleware\AuthnMiddleware::class);

    return $app;
};

