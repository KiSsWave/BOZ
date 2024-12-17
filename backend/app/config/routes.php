<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function( \Slim\App $app):\Slim\App {

    $app->add(\backend\app\src\application\middleware\CorsMiddleware::class);

    $app->group('', function () use ($app){

    })->add(\backend\app\src\application\middleware\AuthnMiddleware::class);

    return $app;
};

