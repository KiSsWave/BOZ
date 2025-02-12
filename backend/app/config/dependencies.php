<?php

require_once __DIR__ . '/../vendor/autoload.php';

use boz\application\action\CreateFactureAction;
use boz\application\action\GetBalanceAction;
use boz\application\action\GetHistoryAction;
use boz\application\action\PayFactureAction;
use boz\application\action\RegisterAction;
use boz\application\action\SignInAction;
use boz\application\middleware\AuthnMiddleware;
use boz\application\middleware\AuthzUserMiddleware;
use boz\application\middleware\AuthzAdminMiddleware;
use boz\application\middleware\AuthzVendeurMiddleware;
use boz\application\providers\AuthnProviderInterface;
use boz\application\providers\JWTAuthnProvider;
use boz\application\providers\JWTManager;
use boz\core\repositoryInterfaces\UserRepositoryInterface;
use boz\core\services\auth\AuthnService;
use boz\core\services\auth\AuthnServiceInterface;
use boz\core\services\auth\AuthzService;
use boz\core\services\Blockchain\BlockService;
use boz\core\services\auth\AuthzServiceInterface;
use boz\core\services\tickets\TicketService;
use boz\Infrastructure\repositories\BlockRepository;
use boz\Infrastructure\repositories\TicketRepository;
use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;
use boz\application\middleware\CorsMiddleware;
use boz\infrastructure\repositories\UserRepository;
use boz\core\services\Blockchain\BlockServiceInterface;
use boz\core\repositoryInterfaces\BlockRepositoryInterface;
use boz\core\services\tickets\TicketServiceInterface;
use boz\core\repositoryInterfaces\TicketRepositoryInterface;
use boz\application\action\AddTicketAction;
use boz\application\action\GetTicketByAdminIdAction;
use boz\application\action\TakeTicketByAdminAction;
use boz\application\action\CloseGameAction;
use boz\application\action\GetTicketByUserIdAction;

return [
    'dotenv' => function () {
        $dotenv = Dotenv::createImmutable(__DIR__ , ['.env','dbconnexion.env']);
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

    JWTManager::class => function () {
        return new JWTManager();
    },

    BlockRepositoryInterface::class => function (ContainerInterface $container) {
        return new BlockRepository($container->get(PDO::class));
    },

    UserRepositoryInterface::class => function(ContainerInterface $c){
      return new UserRepository($c->get(PDO::class));
    },

    TicketRepositoryInterface::class => function(ContainerInterface $c){
    return new TicketRepository($c->get(PDO::class));
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

    BlockServiceInterface::class =>function(ContainerInterface $c){
      return new BlockService($c->get(BlockRepositoryInterface::class));
    },

    TicketServiceInterface::class =>function(ContainerInterface $c){
    return new TicketService($c->get(TicketRepositoryInterface::class));
    },

    AuthnMiddleware::class =>function (ContainerInterface $c){
        return new AuthnMiddleware($c->get(AuthnProviderInterface::class));
    },

    AuthzUserMiddleware::class =>function (ContainerInterface $c){
        return new AuthzUserMiddleware($c->get(AuthzServiceInterface::class));
    },

    AuthzAdminMiddleware::class =>function (ContainerInterface $c){
        return new AuthzAdminMiddleware($c->get(AuthzServiceInterface::class));
    },

    AuthzVendeurMiddleware::class =>function (ContainerInterface $c){
        return new AuthzVendeurMiddleware($c->get(AuthzServiceInterface::class));
    },

    SignInAction::class => function (ContainerInterface $c){
        return new SignInAction($c->get(AuthnProviderInterface::class));
    },

    RegisterAction::class => function (ContainerInterface $c){
        return new RegisterAction($c->get(AuthnProviderInterface::class));
    },

    GetBalanceAction::class => function (ContainerInterface $container) {
        return new GetBalanceAction($container->get(BlockServiceInterface::class));
    },

    GetHistoryAction::class => function (ContainerInterface $container) {
        return new GetHistoryAction($container->get(BlockServiceInterface::class));
    },

    AddTicketAction::class => function (ContainerInterface $c){
    return new AddTicketAction($c->get(TicketServiceInterface::class));
    },

    GetTicketByAdminIdAction::class => function (ContainerInterface $c){
    return new GetTicketByAdminIdAction($c->get(TicketServiceInterface::class));
    },

    TakeTicketByAdminAction::class => function(ContainerInterface $c){
    return new TakeTicketByAdminAction($c->get(TicketServiceInterface::class));
    },

    CloseGameAction::class => function (ContainerInterface $c){
        return new CloseGameAction($c->get(TicketServiceInterface::class));
    },

    GetTicketByUserIdAction::class => function (ContainerInterface $c) {
        return new GetTicketByUserIdAction($c->get(TicketServiceInterface::class));
    },
    CreateFactureAction::class => function (ContainerInterface $container) {
        return new CreateFactureAction($container->get(BlockServiceInterface::class));
    },

    PayFactureAction::class => function (ContainerInterface $container) {
        return new PayFactureAction($container->get(BlockServiceInterface::class));
    },
];
