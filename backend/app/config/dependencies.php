<?php

require_once __DIR__ . '/../vendor/autoload.php';

use backend\app\src\application\action\RegisterAction;
use backend\app\src\application\action\SignInAction;
use backend\app\src\application\providers\AuthnProviderInterface;
use backend\app\src\application\providers\JWTAuthnProvider;
use backend\app\src\application\providers\JWTManager;
use backend\app\src\core\repositoryInterfaces\UserRepositoryInterface;
use backend\app\src\core\services\auth\AuthnService;
use backend\app\src\core\services\auth\AuthnServiceInterface;
use backend\app\src\core\services\auth\AuthzService;
use backend\app\src\core\services\auth\AuthzServiceInterface;
use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;
use backend\app\src\application\middleware\CorsMiddleware;

return [
    'dotenv' => function () {
        $dotenv = Dotenv::createImmutable(__DIR__ , ['dbconnexion.env']);
        $dotenv->load();
        return $dotenv;
    },


    'db.config' => function () {
        return [
            'dsn' => "pgsql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=boz",
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
        ];
    },

    PDO::class => function (ContainerInterface $container) {
        $config = $container->get('db.config');

        try {
            $pdo = new PDO($config['dsn'], $config['user'], $config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (Exception $e) {
            throw new RuntimeException("Erreur lors de la connexion à la base de données : " . $e->getMessage());
        }
    },

    CorsMiddleware::class => function (){
        return new CorsMiddleware();
    },

    JWTManager::class => function (ContainerInterface $container) {
        return new JWTManager($container->get('jwt.secret'));
    },

    AuthnServiceInterface::class => function (ContainerInterface $c){
        return new AuthnService($c->get(UserRepositoryInterface::class));
    },

    AuthnProviderInterface::class =>function (ContainerInterface $c){
        return new JWTAuthnProvider($c->get(JWTManager::class), $c->get(AuthnServiceInterface::class));
    },

    AuthzServiceInterface::class => function (ContainerInterface $c) {
        return new AuthzService($c->get(UserRepositoryInterface::class));
    },

    AuthnMiddleware::class =>function (ContainerInterface $c){
        return new AuthnMiddleware($c->get(AuthnProviderInterface::class));
    },

    AuthzUserMiddleware::class =>function (ContainerInterface $c){
        return new AuthzUserMiddleware($c->get(AuthzServiceInterface::class));
    },

    AuthzOrganisateurMiddleware::class =>function (ContainerInterface $c){
        return new AuthzOrganisateurMiddleware($c->get(AuthzServiceInterface::class));
    },

    SignInAction::class => function (ContainerInterface $c){
        return new SignInAction($c->get(AuthnProviderInterface::class));
    },

    RegisterAction::class => function (ContainerInterface $c){
        return new RegisterAction($c->get(AuthnProviderInterface::class));
    },






];










