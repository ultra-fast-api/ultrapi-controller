<?php

declare(strict_types=1);

namespace UpiCore\Controller\Interfaces;

interface ControllerBridgeInterface
{
    public function withClient(\UpiCore\Router\Interfaces\ServerRequestInterface $client): void;

    public function withRouterContext(\UpiCore\Router\RouterContext $routerContext): void;
}
