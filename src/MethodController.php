<?php

declare(strict_types=1);

namespace UpiCore\Controller;

use UpiCore\Controller\Utils\MethodValidator;
use UpiCore\Exception\UpiException;
use UpiCore\Router\RouterContext;

class MethodController implements \UpiCore\Controller\Interfaces\ControllerInterface, \UpiCore\Controller\Interfaces\ControllerBridgeInterface
{

    use \UpiCore\Controller\Traits\ControllerBridgeTrait;

    /**
     * Current Method Manager
     *
     * @var \UpiCore\Controller\MethodManager
     */
    protected $methodManager;

    /**
     * Matching method file name pattern
     *
     * @var string
     */
    private $methodRegExp = '/^(?:[\w-]+\/){0,24}[\w-]+$/';

    public function __construct(\UpiCore\Router\Interfaces\ServerRequestInterface $client, RouterContext $routerContext)
    {
        $this->client = $client;
        $this->routerContext = $routerContext;

        if (!$requestMethod = $this->getRequestMethod()) {
            throw new UpiException('METHOD_REQUEST_METHOD_REQUIRED');
        }

        if (!$method = $this->isMethod($requestMethod)) {
            throw new UpiException('METHOD_REQUEST_METHOD_NOT_FOUND');
        }

        $this->createMethodManager(
            MethodValidator::newMethodInstance($method)
        );
    }

    protected function createMethodManager($obj): void
    {

        $this->methodManager = (new \UpiCore\Controller\MethodManager($obj))->withClient($this->client)->withRouterContext($this->routerContext);
    }

    /**
     * Template method to handle method
     *
     * @return RouterContext
     * @throws UpiException
     */
    public function __invoke(\Closure $closure = null): RouterContext
    {
        $queryParams = $this->client->getQueryParams();

        $this->removeRequestMethodParams($queryParams);

        $this->validateMethodAccessibility($this->methodManager);
        $closure($this->methodManager);

        $iterator = new \UpiCore\Controller\Utils\MethodIterator($queryParams['data'] ?? []);
        $reqData = $this->collectRequestData($this->methodManager, $iterator);

        /**
         * Method Registration via `_register`
         *
         * The `_register` method handles the ability to use methods within other methods.
         * Only arguments that accept instances of MethodController are accepted here.
         */
        if ($this->methodManager->methodExists('_register')) {
            $registerMethod = $this->methodManager->getMethod('_register');

            $methodManagers = [];
            foreach ($registerMethod->getParameters() as $param) {
                foreach ($param->getAttributes() as $attr) {
                    $attrName = $attr->getName();
                    list($attrRequestMethod) = $attr->getArguments();

                    if (
                        $attrName === 'UpiCore\Controller\MethodManager' && $methodFile = MethodValidator::checkMethodDestination($attrRequestMethod)
                    ) {
                        $methodManagers[] = (new \ReflectionClass($attrName))->newInstance(
                            MethodValidator::newMethodInstance($methodFile)
                        );
                    } else {
                        throw new UpiException('METHOD_REQUEST_METHOD_DONT_REGISTER', $attrRequestMethod);
                    }
                }
            }

            $this->methodManager->_register(...\array_values($methodManagers));
        }

        if ($this->methodManager->methodExists('_beforeResult')) {
            $this->methodManager->callMethod('_beforeResult');
        }

        $methodResult = $this->methodManager->callDefinedResult(...\array_values($reqData));

        if ($this->methodManager->methodExists('_afterResult')) {
            $this->methodManager->callMethod('_afterResult');
        }

        return $methodResult;
    }

    protected function getRequestMethod(): string
    {
        $queryParams = $this->client->getQueryParams();

        $requestMethod = $queryParams['data']['requestMethod'] ?? $queryParams['data']['req'] ?? null;
        if (\is_null($requestMethod) || !$this->checkMethod($requestMethod)) {
            throw new UpiException('METHOD_REQUEST_METHOD_NOT_VALID');
        }

        return $requestMethod;
    }

    protected function checkMethod(string $requestMethod): bool
    {
        return (bool) preg_match_all($this->methodRegExp, $requestMethod);
    }

    protected function isMethod(string $requestMethod): bool|string
    {
        $pattern = '/^((?:[\w-]+\/)*)([\w-]+)$/';
        $replacement = '$1' . $this->client->getRequestMethod() . '_$2';
        $frame = preg_replace($pattern, $replacement, $requestMethod);

        return MethodValidator::checkMethodDestination($frame);
    }

    protected function removeRequestMethodParams(array &$queryParams): void
    {
        unset($queryParams['data']['requestMethod'], $queryParams['data']['req']);
    }

    protected function validateMethodAccessibility($methodManager): void
    {
        if ($methodManager->isInaccessible()) {
            throw new UpiException('METHOD_METHOD_INACCESSIBLE');
        }

        if (!$methodManager->resultIsDefined()) {
            throw new UpiException('METHOD_METHOD_DONT_HAVE_RESULT');
        }
    }

    protected function collectRequestData($methodManager, $iterator): array
    {
        $methodParameters = $methodManager->getDefinedResultParameters();
        $reqData = [];

        foreach ($methodParameters as $parameter) {
            $paramName = $parameter->getName();
            $paramValue = $iterator->offsetGet($paramName);

            // Parametre değeri null ve opsiyonel değilse hata at
            if ($paramValue === null && !$parameter->isOptional()) {
                if ($parameter->hasType() && $parameter->getType()->allowsNull()) {
                    $reqData[$paramName] = null;
                    continue;
                }

                throw new UpiException('METHOD_API_PARAMS_ERR');
            }

            // Parametre değeri null değilse veya opsiyonel ise değeri ayarla
            if ($paramValue !== null) {
                $reqData[$paramName] = $paramValue;
            } else {
                $iterator->offsetSet($paramName, $parameter->getDefaultValue());

                $reqData[$paramName] = $iterator->offsetGet($paramName);
            }

            // Parametre tipini kontrol et
            if ($parameter->hasType() && $reqData[$paramName] !== null) {
                $builtInTypes = $parameter->getType()->__toString();
                $builtInTypesArr = explode('|', $builtInTypes[0] === '?' ? substr($builtInTypes . '|null', 1) : $builtInTypes);

                if (!in_array($iterator->type($paramName), $builtInTypesArr) && $parameter->getType()->getName() !== 'mixed') {
                    throw new UpiException('METHOD_API_VAR_TYPE_ERR', $paramName, '[' . implode('|', $builtInTypesArr) . ']');
                }
            }
        }

        return $reqData;
    }
}
