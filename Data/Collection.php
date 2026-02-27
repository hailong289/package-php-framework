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
    
    public function sortBy($key, $direction = 'asc')
    {
        $asArray = (array)$this->data;
        usort($asArray, function ($a, $b) use ($key, $direction) {
            $valueA = is_object($a) ? ($a->{$key} ?? null) : ($a[$key] ?? null);
            $valueB = is_object($b) ? ($b->{$key} ?? null) : ($b[$key] ?? null);
            if ($valueA == $valueB) {
                return 0;
            }
            if ($direction === 'asc') {
                return ($valueA < $valueB) ? -1 : 1;
            } else {
                return ($valueA > $valueB) ? -1 : 1;
            }
        });
        $this->data = (object)$asArray;
        return $this;
    }

    public function sortByDesc($key) {
        return $this->sortBy($key, 'desc');
    }

    public function sortByAsc($key) {
        return $this->sortBy($key, 'asc');
    }

    public function group($key)
    {
        $asArray = (array)$this->data;
        $grouped = [];
        foreach ($asArray as $item) {
            $groupKey = is_object($item) ? ($item->{$key} ?? null) : ($item[$key] ?? null);
            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [];
            }
            $grouped[$groupKey][] = $item;
        }
        $this->data = (object)$grouped;
        return $this;
    }

    public function limit($limit) {
        $asArray = (array)$this->data;
        $limited = array_slice($asArray, 0, $limit);
        $this->data = (object)$limited;
        return $this;
    }

    public function offset($offset) {
        $asArray = (array)$this->data;
        $offsetted = array_slice($asArray, $offset);
        $this->data = (object)$offsetted;
        return $this;
    }

    public function except($keys)
    {
        $asArray = (array)$this->data;
        foreach ($keys as $key) {
            unset($asArray[$key]);
        }
        $this->data = (object)$asArray;
        return $this;
    }

    public function only($keys) {
        $asArray = (array)$this->data;
        $only = [];
        foreach ($keys as $key) {
            if (isset($asArray[$key])) {
                $only[$key] = $asArray[$key];
            }
        }
        $this->data = (object)$only;
        return $this;
    }

    public function exists($key)
    {
        $asArray = (array)$this->data;
        return isset($asArray[$key]);
    }

    public function merge($data)
    {
        $asArray = (array)$this->data;
        $newData = $this->convertToObject($data);
        $merged = array_merge($asArray, (array)$newData);
        $this->data = (object)$merged;
        return $this;
    }

    public function union()
    {
        $asArray = (array)$this->data;
        $newData = $this->convertToObject(func_get_args());
        $union = array_merge($asArray, (array)$newData);
        $union = array_unique($union);
        $this->data = (object)$union;
        return $this;
    }
}