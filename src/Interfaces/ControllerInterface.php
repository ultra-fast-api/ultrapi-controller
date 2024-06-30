<?php

declare(strict_types=1);

namespace UpiCore\Controller\Interfaces;

interface ControllerInterface
{
    public function __invoke(\Closure $closure = null): \UpiCore\Router\Interfaces\ResponseInterface;
}
