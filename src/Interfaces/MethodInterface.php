<?php

declare(strict_types=1);

namespace UpiCore\Controller\Interfaces;

interface MethodInterface
{
    public function setProvider(\UpiCore\Controller\MethodProvider $methodProvider): void;
}
