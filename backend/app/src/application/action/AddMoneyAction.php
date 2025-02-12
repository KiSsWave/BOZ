
*<?php

namespace backend\app\src\application\action;

use boz\application\action\AbstractAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AddMoneyAction extends AbstractAction
{

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        // TODO: Implement __invoke() method.
    }
}