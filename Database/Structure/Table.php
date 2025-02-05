<?php

namespace Hola\Database\Structure;

class Table extends DataType {
    private $isUse = false;
    private $category = [
        'ADD',
        'MODIFY',
        'DROP',
        'CHANGE',
    ];
    public function __construct($isUse = false)
    {
        $this->isUse = $isUse;
    }

    /**
     * @param string $name
     * @param string $type
     * @return AttributeType
     */
    public function resloveColumn($name, $type)
    {
        $this->columns[$name] = [
            'name' => $name,
            'type' => $type,
            'callback' => $this->getAtributes($name)
        ];
        return $this->columns[$name]['callback'];
    }
    
    public function toSql()
    {
        $sql = [];
        foreach ($this->columns as $column) {
            $category = '';
            $attributesCategory = 'ADD';
            $attributesIndexString = '';
            $attributesString = '';
            $attributes = get_object_vars($column['callback']);
            $length = $this->reloveLength($attributes);
            $attributesDefault = $this->reloveAtributes($attributes);
            $attributesIndex = $this->reloveAtributesIndex($attributes);
            $attributesForeignKey = $this->reloveAtributesForeignKey($attributes);

            if (!empty($attributesIndex)) {
                $attributesIndexString = implode(' ', $attributesIndex);
            }

            if (!empty($attributesForeignKey)) {
                $attributesForeignKeyString = implode(' ', $attributesForeignKey);
            }

            if (!empty($attributesDefault)) {
                $attributesString = implode(' ', $attributesDefault);
            }

            if (!$this->isUse) {
                $sql[] = $column['name'] . ' ' . $column['type'] . $length . ' ' . $attributesString;
                if (!empty($attributesIndex)) {
                    $sql[] = $attributesIndexString;
                }
                if (!empty($attributesForeignKey)) {
                    $sql[] = $attributesForeignKeyString;
                }
            } else {
                $sqlString = '';
                $attributesCategory = $attributes['category'] ?? 'ADD';
                switch ($attributesCategory) {
                    case 'ADD':
                        $category = 'ADD COLUMN';
                        $sqlString = $column['name'] . ' ' . $column['type'] . $length . ' ' . $attributesString;
                        break;
                    case 'MODIFY':
                        $category = 'MODIFY COLUMN';
                        $sqlString = $column['name'] . ' ' . $column['type'] . $length . ' ' . $attributesString;
                        break;
                    case 'DROP':
                        $category = 'DROP COLUMN';
                        $sqlString = $column['name'];
                        break;
                    case 'CHANGE':
                        $category = 'CHANGE COLUMN';
                        $sqlString = $column['name'] . ' ' . $attributes['new_name'] . ' ' . $column['type'] . $length . ' ' . $attributesString;
                        break;
                }
                $sql[] = $category . ' ' . $sqlString;

                if (!empty($attributesIndex) && in_array($column['category'], ['ADD', 'DROP'])) {
                    $sql[] = $column['category'] . $attributesIndexString;
                }

                if (!empty($attributesForeignKey) && in_array($column['category'], ['ADD', 'DROP'])) {
                    $sql[] = $column['category'] . $attributesForeignKeyString;
                }
            }
        }
        return implode(', ', $sql);
    }

    private function reloveLength($attributes = [])
    {
        $filtered = array_intersect_key($attributes, array_flip(['value']));
        return $filtered['value'] ?? '';
    }


    private function reloveAtributes($values = [])
    {
        $not_get = [
            'index',
            'value',
            'category',
            'new_name',
            'foreign_key',
            'references',
            'on_delete',
            'on_update',
        ];
        $attributes = array_filter($values, function ($value, $key) use ($not_get) {
            return !in_array($key, $not_get);
        }, ARRAY_FILTER_USE_BOTH);
        return empty($attributes) ? [] : $attributes;
    }

    private function reloveAtributesIndex($values = [])
    {
        $attributes = array_filter($values, function ($value, $key) {
            return  $key === 'index';
        }, ARRAY_FILTER_USE_BOTH);
        return empty($attributes) ? [] : $attributes;
    }

    private function reloveAtributesForeignKey($values = [])
    {
        $get = [
            'foreign_key',
            'references',
            'on_delete',
            'on_update',
        ];
        $attributes = array_filter($values, function ($value, $key) use ($get) {
            return in_array($key, $get);
        }, ARRAY_FILTER_USE_BOTH);
        return empty($attributes) ? [] : $attributes;
    }
}