<?php

declare(strict_types=1);

namespace UpiCore\Controller;

use UpiCore\Exception\UpiException;

class Method
{
    /**
     * Methods files destination
     *
     * @var string
     */
    protected static $methodsPath = null;

    public function __construct(string $methodsPath = null)
    {
        if (!$methodsPath && !$defaultPath = \UpiCore\Ceremony\Utils\Destination\PathResolver::methodsPath()) {
            throw new UpiException('METHOD_LOCALE_PATH_NOT_DEFINED');
        }

        self::$methodsPath = $methodsPath ?: $defaultPath;

        if (!\is_dir(self::$methodsPath)) {
            throw new UpiException('METHOD_LOCALE_PATH_NOT_EXISTS');
        }
    }

    /**
     * Gives current Methods path
     *
     * @return string|null
     */
    public static function getMethodsPath()
    {
        return self::$methodsPath;
    }

    
}
