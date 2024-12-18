<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function( \Slim\App $app):\Slim\App {

    $app->add(\backend\app\src\application\middleware\CorsMiddleware::class);

    $app->post('signin', backend\app\src\application\action\SignInAction::class);
    $app->post('register', backend\app\src\application\action\RegisterAction::class);
    $app->get('balance', backend\app\src\application\action\GetBalanceAction::class);
    $app->get('history', backend\app\src\application\action\GetHistoryAction::class);

    $app->group('', function () use ($app){

    })->add(\backend\app\src\application\middleware\AuthnMiddleware::class);

    return $app;
};

