<?php

namespace Hola\Database\Structure;

class AttributeType {
    private $attributes;
    private $column;
    public $category = 'ADD';
    
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
        $this->default = "DEFAULT '$value'";
        return $this;
    }

    public function defaultCurrentTimestamp()
    {
        $this->default = "DEFAULT CURRENT_TIMESTAMP";
        return $this;
    }
    
    public function comment($value)
    {
        $this->comment = "COMMENT '$value'";
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

    public function foreignKey($name = '')
    {
        $foreignKey = $name ? $name : "fk_{$this->column}";
        $this->foreign_key = "CONSTRAINT $foreignKey FOREIGN KEY ($this->column)";
        return $this;
    }

    public function references($table, $column)
    {
        $this->references = "REFERENCES $table($column)";
        return $this;
    }

    public function onDelete($action)
    {
        $this->on_delete = "ON DELETE $action";
        return $this;
    }

    public function onUpdate($action)
    {
        $this->on_update = "ON UPDATE $action";
        return $this;
    }

    public function modify() {
        $this->category = 'MODIFY';
        return $this;
    }

    public function add() {
        $this->category = 'ADD';
        return $this;
    }

    public function drop() {
        $this->category = 'DROP';
        return $this;
    }

    public function change($newName = null) {
        $this->category = 'CHANGE';
        if (is_null($newName)) {
            $newName = $this->column;
        }
        $this->new_name = $newName;
        return $this;
    }

}