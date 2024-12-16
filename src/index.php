<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Blockchain\Blockchain;
use Blockchain\Transaction;

$blockchain = new Blockchain();

try {

    $transaction1 = new Transaction("Gaetan", 500.0, "add");
    $blockchain->addBlock($transaction1);
    echo "Transaction 1 ajoutée avec succès." . PHP_EOL;

    $transaction2 = new Transaction("Gaetan", 200.0, "pay");
    $blockchain->addBlock($transaction2);
    echo "Transaction 2 ajoutée avec succès." . PHP_EOL;

    $transaction4 = new Transaction("Jeandidi", 400.0, "pay");
    try {
        $blockchain->addBlock($transaction4);
        echo "Transaction 3 ajoutée avec succès." . PHP_EOL;
    } catch (Exception $e) {
        echo "Erreur avec la transaction 3 : " . $e->getMessage() . PHP_EOL;
    }

    $transaction3 = new Transaction("Gaetan", 400.0, "pay");
    try {
        $blockchain->addBlock($transaction3);
        echo "Transaction 4 ajoutée avec succès." . PHP_EOL;
    } catch (Exception $e) {
        echo "Erreur avec la transaction 4 : " . $e->getMessage() . PHP_EOL;
    }

} catch (Exception $e) {
    echo "Erreur générale : " . $e->getMessage() . PHP_EOL;
}

echo "Solde de Gaetan : " . $blockchain->getBalance("Gaetan") . PHP_EOL;
echo "Solde de Jeandidi : " . $blockchain->getBalance("Jeandidi") . PHP_EOL;

