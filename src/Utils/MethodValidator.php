<?php

declare(strict_types=1);

namespace UpiCore\Controller\Utils;

class MethodValidator
{
    private static $methodsDestination = null;

    public function __construct()
    {
        self::$methodsDestination = (new \UpiCore\Controller\Method())->getMethodsPath();
    }

    public static function instance(): self
    {
        return new self();
    }

    /**
     * Checks the method file on the file system and returns the method path.
     *
     * @param string $methodPath
     * 
     * @return string|false
     */
    public static function checkMethodDestination(string $methodPath): string|false
    {
        $self = self::instance();

        $file = sprintf(
            '%s' . DIRECTORY_SEPARATOR . '%s',
            $self::$methodsDestination,
            \preg_replace('/\//', DIRECTORY_SEPARATOR, $methodPath) . '.php'
        );

        $realpath = \realpath($file);

        if (!$realpath || !\is_file($realpath)) {
            return false;
        }

        return $file;
    }

    public static function newMethodInstance(string $methodPath): \UpiCore\Controller\Interfaces\MethodInterface
    {
        $obj = require $methodPath;

        if (!$obj instanceof \UpiCore\Controller\Interfaces\MethodInterface) {
            throw new \UpiCore\Exception\UpiException('METHOD_HANDLING_INTERFACE_ERR');
        }

        return $obj;
    }
}
