<?php

declare(strict_types=1);

namespace UpiCore\Controller;

use UpiCore\Exception\UpiException;

class MethodManager
{
    /**
     * Defined Anonymous Method Object
     *
     * @var object
     */
    protected $methodInstance;

    public function __construct(\UpiCore\Controller\Interfaces\MethodInterface $anonymousMethod)
    {
        if (!$anonymousMethod instanceof \UpiCore\Controller\Interfaces\MethodInterface) {
            throw new UpiException('METHOD_HANDLING_INTERFACE_ERR');
        }

        $this->methodInstance = $anonymousMethod;

        // if ($methodCounstructor)
        //     $this->methodInstance->setProvider($methodCounstructor);
    }

    public function withClient(\UpiCore\Router\Http\Client $client): self
    {
        $new = clone $this;
        $new->methodInstance->withClient($client);

        return $new;
    }

    public function withRouterContext(\UpiCore\Router\RouterContext $routerContext): self
    {
        $new = clone $this;
        $new->methodInstance->withRouterContext($routerContext);

        return $new;
    }

    public function __call($method, $args)
    {
        if (\method_exists($this->methodInstance, $method)) {
            return $this->methodInstance->{$method}(...$args);
        } else {
            throw new UpiException('METHOD_CALL_METHOD_NOT_FOUND', $method);
        }
    }

    public function getInstance(): \UpiCore\Controller\Interfaces\MethodInterface
    {
        return $this->methodInstance;
    }

    public function getDefinedMethod(): \ReflectionMethod
    {
        return $this->getMethod('_toResult');
    }

    public function getMethod(string $method): \ReflectionMethod
    {
        return (new \ReflectionMethod($this->methodInstance, $method));
    }

    public function resultIsDefined(): bool
    {
        return $this->methodExists('_toResult');
    }

    public function getDefinedResultParameters()
    {
        return $this->getDefinedMethod()->getParameters();
    }

    public function callDefinedResult(...$args): \UpiCore\Router\RouterContext
    {
        return $this->methodInstance->_toResult(...$args);
    }

    public function isEveryoneAccess(): bool
    {
        return \property_exists($this->methodInstance, 'everyoneAccess');
    }

    public function isInaccessible(): bool
    {
        return \property_exists($this->methodInstance, 'inaccessible');
    }

    public function callMethod(string $methodName, ...$args)
    {
        if (!$this->methodExists($methodName))
            return NULL;

        return $this->methodInstance->{$methodName}(...$args);
    }

    public function methodExists(string $methodName): bool
    {
        return \method_exists($this->methodInstance, $methodName);
    }
}
