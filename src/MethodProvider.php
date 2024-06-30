<?php

declare(strict_types=1);

namespace UpiCore\Controller;

class MethodProvider
{

    private static $objects;

    public function __construct(...$coreModules)
    {
        self::$objects = new \stdClass();
        foreach ($coreModules as $module) {
                if (\class_exists($module)) {
                $moduleName = \explode('\\', $module);
                self::$objects->{$moduleName[count($moduleName) - 1]} = (new \ReflectionClass($module))->newInstance();
            }
        }
    }

    public static function getObjects()
    {
        return self::$objects;
    }

    public function classExists(string $class)
    {
        return self::$objects->{$class} ?? null;
    }

    public function getCoreClass(string $class)
    {
        if ($this->classExists($class))
            return self::$objects->{$class} ?? null;
    }

    public static function __callStatic($method, $args)
    {
        return (new self())->getCoreClass($method);
    }
}
