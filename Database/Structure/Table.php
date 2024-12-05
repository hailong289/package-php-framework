<?php

namespace Hola\Database\Structure;

class Table extends DataType {
    private $isUse = false;
    public function __construct($isUse = false)
    {
        $this->isUse = $isUse;
    }
    
    public function addColumn($name, $type): AttributeType
    {
        $this->columns[$name] = [
            'name' => $name,
            'type' => $type,
            'category' => 'ADD',
            'callback' => $this->getAtributes($name)
        ];
        return $this->columns[$name]['callback'];
    }
    
    public function toSql()
    {
        $sql = [];
        foreach ($this->columns as $column) {
            $length = $column['callback']->getLength();
            $attributes = $column['callback']->get();
            $attributesIndex = $column['callback']->getIndex();
            $sql[] = ($this->isUse ? $column['category'] . ' COLUMN ' : '') . $column['name'] . ' ' . $column['type'] . $length . ' ' . implode(' ', $attributes);
            if (!empty($attributesIndex)) {
                $sql[] = ($this->isUse ? $column['category'] : '') . implode(' ', $attributesIndex);
            }
            $column['callback']->clear();
        }
        return implode(', ', $sql);
    }
    
    
    
}