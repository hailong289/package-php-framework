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
            $attributes = get_object_vars($column['callback']);
            $length = $this->reloveLength($attributes);
            $attributesString = $this->reloveAtributes($attributes);
            $attributesIndex = $this->reloveAtributesIndex($attributes);
            $sql[] = ($this->isUse ? $column['category'] . ' COLUMN ' : '') . $column['name'] . ' ' . $column['type'] . $length . ' ' . $attributesString;
            if (!empty($attributesIndex)) {
                $sql[] = ($this->isUse ? $column['category'] : '') . implode(' ', $attributesIndex);
            }
        }
        log_debug($sql);
        return implode(', ', $sql);
    }

    private function reloveLength($attributes = [])
    {
        $filtered = array_intersect_key($attributes, array_flip(['value']));
        return $filtered['value'] ?? '';
    }


    private function reloveAtributes($values = [])
    {
        $attribute = array_filter($values, function ($value, $key) {
            return $key !== 'index' && $key !== 'value';
        }, ARRAY_FILTER_USE_BOTH);
        return implode(' ', $attribute);
    }

    private function reloveAtributesIndex($values = [])
    {
        $attributes = array_filter($values, function ($value, $key) {
            return  $key === 'index';
        }, ARRAY_FILTER_USE_BOTH);
        return $attributes;
    }
}