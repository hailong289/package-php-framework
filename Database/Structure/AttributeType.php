<?php

namespace Hola\Database\Structure;

class AttributeType {
    private $attributes;
    private $column;
    
    public function __construct($column)
    {
        $this->column = $column;
    }
    
    public function length($length)
    {
        $this->attributes[0] = "($length)";
        return $this;
    }

    public function values($values)
    {
        $this->attributes[0] = "($values)";
        return $this;
    }
    
    public function unsigned()
    {
        $this->attributes[1] = 'UNSIGNED';
        return $this;
    }
    
    public function autoIncrement()
    {
        $this->attributes[2] = 'AUTO_INCREMENT';
        return $this;
    }
    
    public function primaryKey()
    {
        $this->attributes[3] = 'PRIMARY KEY';
        return $this;
    }
    
    public function unique()
    {
        $this->attributes[4] = 'UNIQUE';
        return $this;
    }
    
    public function notNull()
    {
        $this->attributes[5] = 'NOT NULL';
        return $this;
    }

    public function null()
    {
        if (!empty($this->attributes[5])) {
            return $this;
        }
        $this->attributes[6] = 'DEFAULT NULL';
        return $this;
    }
    
    public function default($value)
    {
        $this->attributes[6] = "DEFAULT $value";
        return $this;
    }
    
    public function comment($value)
    {
        $this->attributes[7] = "COMMENT $value";
        return $this;
    }
    
    public function index($name = '')
    {
        $index_name = $name ? $name : "idx_{$this->column}";
        $this->attributes[8] = "INDEX $index_name({$this->column})";
        return $this;
    }
    
    public function fullTextIndex($name = '')
    {
        $index_name = $name ? $name : "idx_{$this->column}";
        $this->attributes[8] = "FULLTEXT INDEX $index_name({$this->column})";
        return $this;
    }

    public function spatialIndex($name = '')
    {
        $index_name = $name ? $name : "idx_{$this->column}";
        $this->attributes[8] = "SPATIAL INDEX $index_name({$this->column})";
        return $this;
    }

    public function get()
    {
        $attribute = array_filter($this->attributes ?? [], function ($value, $key) {
            return $key !== 8 && $key !== 0;
        }, ARRAY_FILTER_USE_BOTH);
        return $attribute;
    }
    
    public function getLength()
    {
        return $this->attributes[0] ?? '';
    }

    public function getIndex()
    {
        $attributes = array_filter($this->attributes ?? [], function ($value, $key) {
            return  $key === 8;
        }, ARRAY_FILTER_USE_BOTH);
        return $attributes;
    }
    
    public function clear()
    {
        $this->attributes = [];
        return $this;
    }

}