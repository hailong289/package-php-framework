<?php

namespace Hola\Data;

class Collection
{
    public $data = [];
    public function __construct($data)
    {
        $this->data = is_object($data) ? $data : json_decode(json_encode($data));
    }
    
    public function set($data) {
        $this->data = $data;
        return $this;
    }

    public function toArray() {
        $this->data = is_array($this->data) ? $this->data : json_decode(json_encode($this->data), true);
        return $this->data;
    }

    public function toObject() {
        $this->data = is_object($this->data) ? $this->data : json_decode(json_encode($this->data));
        return $this->data;
    }

    public function values() {
        return $this->data;
    }

    public function value($key = null) {
        $data = $this->count() ? $this->data[0] : $this->data;
        if (is_null($key)) {
            return $data;
        }
        if (is_array($data)) {
            return isset($data[$key]) ? $data[$key] : null;
        } elseif (is_object($data)) {
            return isset($data->{$key}) ? $data->{$key} : null;
        } else {
            return null;
        }
    }

    public function count(): int
    {
        $is_count = is_countable($this->data) && count($this->data);
        return $is_count ? count($this->data):0;
    }

    function flat()
    {
        $return = [];
        array_walk_recursive($this->toArray(), function($a) use (&$return) { $return[] = $a; });
        return $return;
    }

    public function isEmpty()
    {
        return empty($this->data);
    }

    public function keys()
    {
        $data = is_object($this->data) ? (array)$this->data:$this->data;
        return array_keys($data);
    }

    public function map($fn) {
        foreach ($this->data as $key => $data) {
            $this->data[$key] = $fn($data);
        }
        return $this;
    }

    public function forEach($fn) {
        foreach ($this->data as $key => $data) {
            $fn($data, $key);
        }
        return $this;
    }

    public function dataColumn($key)
    {
        foreach ($this->data as $key_data => $data) {
            $keys = get_object_vars($data);
            if(isset($keys[$key])) {
                $this->data[$key_data] = $data->{$key};
            }
        }
        return $this;
    }

    public function mapFirst($fn) {
        $this->data = $fn($this->data);
        return $this;
    }

    public function filter($fn) {
        foreach ($this->data as $key => $data) {
            if($fn($data)) {
                $this->data[$key] = $data;
            } else {
                if (is_array($this->data)) {
                    unset($this->data[$key]);
                } else {
                    unset($this->data->{$key});
                }
            }
        }
        return $this;
    }

    public function push(...$values)
    {
        foreach ($values as $value) {
            $this->data[] = $value;
        }
        return $this;
    }

    public function add($item)
    {
        $this->data[] = $item;

        return $this;
    }

    public function last()
    {
        return $this->count() ? $this->data[$this->count() - 1]:$this->data;
    }

    public function chunk($number, $callback = null)
    {
        $chunk = array_chunk($this->data, $number);
        if (is_callable($callback)) {
            $callback($chunk);
            return;
        }
        $this->data = $chunk;
        return $this;
    }
}