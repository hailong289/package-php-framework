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
        $this->value = "($length)";
        return $this;
    }

    public function values($values)
    {
        $this->value = "($values)";
        return $this;
    }
    
    public function unsigned()
    {
        $this->unsigned = 'UNSIGNED';
        return $this;
    }
    
    public function autoIncrement()
    {
        $this->autoIncrement = 'AUTO_INCREMENT';
        return $this;
    }
    
    public function primaryKey()
    {
        $this->primaryKey = 'PRIMARY KEY';
        return $this;
    }
    
    public function unique()
    {
        $this->unique = 'UNIQUE';
        return $this;
    }
    
    public function notNull()
    {
        $this->notNull = 'NOT NULL';
        return $this;
    }

    public function null()
    {
        if (!empty($this->notNull)) {
            return $this;
        }
        $this->default = 'DEFAULT NULL';
        return $this;
    }
    
    public function default($value)
    {
        $this->default = "DEFAULT $value";
        return $this;
    }
    
    public function comment($value)
    {
        $this->comment = "COMMENT $value";
        return $this;
    }
    
    public function index($name = '')
    {
        $index_name = $name ? $name : "idx_{$this->column}";
        $this->index = "INDEX $index_name({$this->column})";
        return $this;
    }
    
    public function fullTextIndex($name = '')
    {
        $index_name = $name ? $name : "idx_{$this->column}";
        $this->index = "FULLTEXT INDEX $index_name({$this->column})";
        return $this;
    }

    public function spatialIndex($name = '')
    {
        $index_name = $name ? $name : "idx_{$this->column}";
        $this->index = "SPATIAL INDEX $index_name({$this->column})";
        return $this;
    }

}