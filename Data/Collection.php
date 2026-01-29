<?php

namespace Hola\Data;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use stdClass;

class Collection implements ArrayAccess, IteratorAggregate, Countable, JsonSerializable
{
    /**
     * @var object|stdClass
     */
    protected $data;

    public function __construct($data = [])
    {
        $this->data = $this->convertToObject($data);
    }

    protected function convertToObject($data)
    {
        if ($data instanceof self) {
            return $data->toObject();
        }

        if (is_object($data)) {
            return $data;
        }

        // Optimized: avoid json_encode/decode overhead
        if (is_array($data)) {
            $obj = new stdClass();
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $obj->{$key} = $this->convertToObject($value);
                } else {
                    $obj->{$key} = $value;
                }
            }
            return $obj;
        }

        return $data;
    }

    public function __get($name)
    {
        return $this->data->{$name} ?? null;
    }

    public function __set($name, $value)
    {
        $this->data->{$name} = $value;
    }

    public function __isset($name)
    {
        return isset($this->data->{$name});
    }

    public function __unset($name)
    {
        unset($this->data->{$name});
    }

    public function set($data)
    {
        $this->data = $this->convertToObject($data);
        return $this;
    }

    public function toArray()
    {
        return $this->objectToArray($this->data);
    }

    protected function objectToArray($data)
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (is_array($data)) {
            return array_map([$this, 'objectToArray'], $data);
        }

        return $data;
    }

    public function toObject()
    {
        return $this->data;
    }

    public function values()
    {
        return array_values((array)$this->data);
    }

    public function value($key = null, $default = null)
    {
        $asArray = (array) $this->data;
        
        if (empty($asArray)) {
            return $default;
        }

        $firstKey = array_key_first($asArray);
        $firstItem = $asArray[$firstKey];

        if (is_null($key)) {
            return $firstItem;
        }

        if (is_object($firstItem) && isset($firstItem->{$key})) {
            return $firstItem->{$key};
        }

        if (is_array($firstItem) && isset($firstItem[$key])) {
            return $firstItem[$key];
        }

        return $default;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function count(): int
    {
        return count((array)$this->data);
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }

    public function offsetExists($key): bool
    {
        return isset($this->data->{$key});
    }

    public function offsetGet($key): mixed
    {
        return $this->data->{$key} ?? null;
    }

    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $arrayData = (array) $this->data;
            $arrayData[] = $value;
            $this->data = (object) $arrayData;
        } else {
            $this->data->{$key} = $value;
        }
    }

    public function offsetUnset($key): void
    {
        unset($this->data->{$key});
    }

    public function isEmpty()
    {
        // Optimized: check properties directly for objects
        if (is_object($this->data)) {
            return empty((array)$this->data);
        }
        return empty($this->data);
    }

    public function keys()
    {
        return array_keys((array)$this->data);
    }

    public function map(callable $fn)
    {
        foreach ($this->data as $key => $value) {
            $this->data->{$key} = $fn($value);
        }
        return $this;
    }

    public function forEach(callable $fn)
    {
        foreach ($this->data as $key => $value) {
            $fn($value, $key);
        }
        return $this;
    }

    public function filter(callable $fn)
    {
        $asArray = (array) $this->data;
        $filtered = array_filter($asArray, $fn, ARRAY_FILTER_USE_BOTH);
        $this->data = (object) $filtered;

        return $this;
    }

    public function push(...$values)
    {
        $asArray = (array) $this->data;
        foreach ($values as $value) {
            $asArray[] = $value;
        }
        $this->data = (object) $asArray;
        return $this;
    }

    public function add($item, $key = null)
    {
        if (is_null($key)) {
            $this->push($item);
        } else {
            $this->data->{$key} = $item;
        }
        return $this;
    }

    public function last()
    {
        $asArray = (array) $this->data;
        if (empty($asArray)) {
            return null;
        }
        $lastKey = array_key_last($asArray);
        return $asArray[$lastKey];
    }

    public function chunk($number, $callback = null)
    {
        $chunks = array_chunk((array)$this->data, $number);
        if (is_callable($callback)) {
            foreach ($chunks as $chunk) {
                $callback(new self((object)$chunk));
            }
            return $this;
        }
        $this->data = (object) $chunks;
        return $this;
    }

    public function flat()
    {
        $result = [];
        $asArray = $this->objectToArray($this->data);
        array_walk_recursive($asArray, function ($a) use (&$result) {
            $result[] = $a;
        });
        return $result;
    }

    public function dataColumn($key)
    {
        $asArray = (array)$this->data;
        $result = [];
        foreach ($asArray as $k => $item) {
            if (is_object($item) && isset($item->{$key})) {
                $result[] = $item->{$key};
            } elseif (is_array($item) && isset($item[$key])) {
                $result[] = $item[$key];
            }
        }
        $this->data = (object)$result;
        return $this;
    }

    public function mapFirst(callable $fn)
    {
        $this->data = $this->convertToObject($fn($this->data));
        return $this;
    }
}