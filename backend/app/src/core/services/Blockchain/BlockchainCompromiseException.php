<?php

namespace boz\core\exceptions;

use Exception;

class BlockchainCompromiseException extends Exception
{
    public function __construct(string $message = "Le système de paiement est temporairement indisponible.", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}