<?php

declare(strict_types=1);

namespace UpiCore\Controller;

class UpiMethod implements \UpiCore\Controller\Interfaces\MethodInterface, \UpiCore\Controller\Interfaces\ControllerBridgeInterface
{

    use \UpiCore\Controller\Traits\ControllerBridgeTrait;

    /**
     * Method Cosntructor
     *
     * @var \stdClass
     */
    protected static $Upi;

    public function setProvider(\UpiCore\Controller\MethodProvider $methodProvider): void
    {
        self::$Upi = $methodProvider::getObjects();
    }
}
