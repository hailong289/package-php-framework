<?php
namespace System\Core;

class Collection
{
    private $data = [];
    public function __construct($data)
    {
        $this->data = $data;
        return json_decode(json_encode($this->data));
    }

    public function toArray() {
        $this->data = json_decode(json_encode($this->data), true);
        return $this->data;
    }

    public function toObject() {
        $this->data = json_decode(json_encode($this->data));
        return $this->data;
    }

    public function values() {
        return $this->data;
    }

    public function value() {
        return $this->count() ? $this->data[0]:$this->data;
    }

    public function count(): int
    {
        return is_countable($this->data) && count($this->data);
    }

    public function map($fn) {
        foreach ($this->data as $key => $data) {
            $this->data[$key] = $fn($data);
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
            if($fn($data)) $this->data[$key] = $data;
            else unset($this->data[$key]);
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
        $this->items[] = $item;

        return $this;
    }
}