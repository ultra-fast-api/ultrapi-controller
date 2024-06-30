<?php

declare(strict_types=1);

namespace UpiCore\Controller\Utils;

class MethodIterator implements \ArrayAccess
{

    /**
     * @var array $iterator
     */
    protected $iterator = [];

    protected $typeConverter = [
        'boolean'   => 'bool',
        'integer'   => 'int',
        'double'    => 'float',
        'string'    => 'string',
        'array'     => 'array',
        'object'    => 'object',
    ];

    public function __construct(array $data)
    {
        $this->iterator = $this->filterParams($data);
    }

    /**
     * Filters the parameters received with the request
     * and applies the desired filtering.
     * 
     * @param array $params
     * 
     * @return array
     */
    public function filterParams(array $params): array
    {
        foreach ($params as $key => $param) {
            if (!\is_array($param)) {
                $type = \gettype($param);
                $value = $param; //\htmlspecialchars($param ?? '');
                \settype($value, $type);
                $filtered[$key] = $value;
            } else
                $filtered[$key] = $this->filterParams($param);
        }
        return $filtered ?? [];
    }


    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->iterator[$offset] = $value;
    }

    /**
     * Returns a specified parameter.
     * If the parameter does not exist, returns NULL.
     * 
     * @param mixed $key
     * 
     * @return mixed|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->iterator[$offset] ?? null;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->iterator[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->iterator[$offset]);
    }

    /**
     * Returns the iterator itself.
     * 
     * @return array
     */
    public function iterator(): array
    {
        return $this->iterator;
    }

    /**
     * Returns the data type of the specified parameter.
     * 
     * @param string $key
     * 
     * @return mixed
     */
    public function type(string $key)
    {
        return $this->typeConverter[\gettype($this->iterator[$key] ?? null)] ?? null;
    }
}
