<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dbName = 'boz';
$dotenv = Dotenv::createImmutable(__DIR__ , ['dbconnexion.env']);
$dotenv->load();


$dsn = "pgsql:host=" . $_ENV['DB_HOST'] . ';port=' . $_ENV['DB_PORT'] . ';dbname=' . $dbName;
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "check";
    return $pdo;
} catch (Exception $e) {
    throw new \RuntimeException("Erreur lors de la connexion à la bd : " . $e->getMessage());
}







