<?php

declare(strict_types=1);

namespace UpiCore\Controller\Traits;

trait ControllerBridgeTrait
{
    /**
     * Client Instance
     *
     * @var \UpiCore\Router\Interfaces\ServerRequestInterface
     */
    protected $client;

    /**
     * RouterContext Instance
     *
     * @var \UpiCore\Router\RouterContext
     */
    protected $routerContext;

    public function withClient(\UpiCore\Router\Interfaces\ServerRequestInterface $client): void
    {
        $this->client = $client;
    }

    public function withRouterContext(\UpiCore\Router\RouterContext $routerContext): void
    {
        $this->routerContext = $routerContext;
    }
}
