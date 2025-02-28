<?php

require_once __DIR__ . '/../vendor/autoload.php';

use boz\application\action\ChangeRoleAction;
use boz\application\action\CreateConversationAction;
use boz\application\action\CreateFactureAction;
use boz\application\action\GetBalanceAction;
use boz\application\action\GetConversationsAction;
use boz\application\action\GetFactureByIdAction;
use boz\application\action\GetFacturesByUserLoginAction;
use boz\application\action\GetHistoryAction;
use boz\application\action\GetMessagesAction;
use boz\application\action\GetTicketsPendingAction;
use boz\application\action\GiveCashAction;
use boz\application\action\PayFactureAction;
use boz\application\action\RegisterAction;
use boz\application\action\SendMessageAction;
use boz\application\action\SignInAction;
use boz\application\action\StartConversationFromTicketAction;
use boz\application\action\UpdateProfileAction;
use boz\application\middleware\AuthnMiddleware;
use boz\application\middleware\AuthzUserMiddleware;
use boz\application\middleware\AuthzAdminMiddleware;
use boz\application\middleware\AuthzVendeurMiddleware;
use boz\application\providers\AuthnProviderInterface;
use boz\application\providers\JWTAuthnProvider;
use boz\application\providers\JWTManager;
use boz\core\repositoryInterfaces\ConversationRepositoryInterface;
use boz\core\repositoryInterfaces\UserRepositoryInterface;
use boz\core\services\auth\AuthnService;
use boz\core\services\auth\AuthnServiceInterface;
use boz\core\services\auth\AuthzService;
use boz\core\services\auth\AuthzServiceInterface;
use boz\core\services\conversations\ConversationService;
use boz\core\services\conversations\ConversationServiceInterface;
use boz\core\services\tickets\TicketService;
use boz\Infrastructure\repositories\BlockRepository;
use boz\Infrastructure\repositories\ConversationRepository;
use boz\Infrastructure\repositories\TicketRepository;
use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;
use boz\application\middleware\CorsMiddleware;
use boz\Infrastructure\repositories\UserRepository;
use boz\core\services\Blockchain\BlockServiceInterface;
use boz\infrastructure\repositories\UserRepository;
use boz\core\repositoryInterfaces\BlockRepositoryInterface;
use boz\core\services\tickets\TicketServiceInterface;
use boz\core\repositoryInterfaces\TicketRepositoryInterface;
use boz\application\action\AddTicketAction;
use boz\application\action\GetTicketsByAdminIdAction;
use boz\application\action\TakeTicketByAdminAction;
use boz\application\action\CloseTicketAction;
use boz\application\action\GetTicketsByUserIdAction;

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

    ConversationRepositoryInterface::class => function(ContainerInterface $c){
    return new ConversationRepository($c->get(PDO::class));
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


    TicketServiceInterface::class =>function(ContainerInterface $c){
    return new TicketService($c->get(TicketRepositoryInterface::class));
    },

    ConversationServiceInterface::class => function(ContainerInterface $c){
    return new ConversationService($c->get(ConversationRepositoryInterface::class));
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

    UpdateProfileAction::class => function (ContainerInterface $c) {
        return new UpdateProfileAction($c->get(AuthnServiceInterface::class));
    },

    GetBalanceAction::class => function (ContainerInterface $container) {
        return new GetBalanceAction($container->get(AuthnServiceInterface::class));
    },

    GetHistoryAction::class => function (ContainerInterface $container) {
        return new GetHistoryAction($container->get(AuthnServiceInterface::class));
    },

    AddTicketAction::class => function (ContainerInterface $c){
    return new AddTicketAction($c->get(TicketServiceInterface::class));
    },

    GetTicketsByAdminIdAction::class => function (ContainerInterface $c){
    return new GetTicketsByAdminIdAction($c->get(TicketServiceInterface::class));
    },

    GetTicketsPendingAction::class => function (ContainerInterface $c){
    return new GetTicketsPendingAction($c->get(TicketServiceInterface::class));
    },

    TakeTicketByAdminAction::class => function(ContainerInterface $c){
    return new TakeTicketByAdminAction($c->get(TicketServiceInterface::class));
    },

    CloseTicketAction::class => function (ContainerInterface $c){
        return new CloseTicketAction($c->get(TicketServiceInterface::class));
    },

    GetTicketsByUserIdAction::class => function (ContainerInterface $c) {
        return new GetTicketsByUserIdAction($c->get(TicketServiceInterface::class));
    },
    CreateFactureAction::class => function (ContainerInterface $container) {
        return new CreateFactureAction($container->get(AuthnServiceInterface::class));
    },

    PayFactureAction::class => function (ContainerInterface $container) {
        return new PayFactureAction($container->get(AuthnServiceInterface::class));
    },

    GetFacturesByUserLoginAction::class => function (ContainerInterface $container) {
        return new GetFacturesByUserLoginAction($container->get(AuthnServiceInterface::class));
    },

    GetFactureByIdAction::class => function (ContainerInterface $container) {
        return new GetFactureByIdAction($container->get(AuthnServiceInterface::class));
    },

    CreateConversationAction::class => function (ContainerInterface $c){
        return new CreateConversationAction($c->get(ConversationServiceInterface::class));
    },

    GetConversationsAction::class => function (ContainerInterface $c){
        return new GetConversationsAction($c->get(ConversationServiceInterface::class));
    },

    StartConversationFromTicketAction::class => function (ContainerInterface $c){
        return new StartConversationFromTicketAction($c->get(ConversationServiceInterface::class), $c->get(TicketServiceInterface::class));
    },

    GetMessagesAction::class => function (ContainerInterface $c){
        return new GetMessagesAction($c->get(ConversationServiceInterface::class));
    },

    SendMessageAction::class => function (ContainerInterface $c){
        return new SendMessageAction($c->get(ConversationServiceInterface::class));
    },

    GiveCashAction::class => function (ContainerInterface $c){
        return new GiveCashAction($c->get(AuthnServiceInterface::class));
    },

    ChangeRoleAction::class=>function(ContainerInterface $c){
        return new ChangeRoleAction($c->get(AuthnServiceInterface::class));
    },





];
