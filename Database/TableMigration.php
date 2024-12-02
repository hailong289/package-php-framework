<?php

namespace Hola\Database;

class TableMigration extends TableBuilder {

    public function string($name, $length = 255) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'VARCHAR', $length);
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'VARCHAR';
            $this->bindings['columns'][$name]['length'] = $length;
        }
        return $this;
    }

    public function text($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'TEXT', $length);
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'TEXT';
            $this->bindings['columns'][$name]['length'] = $length;
        }
        return $this;
    }

    public function integer($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'INTEGER');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'INTEGER';
        }
        return $this;
    }

    public function bigInteger($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'BIGINT');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'BIGINT';
        }
        return $this;
    }

    public function tinyInteger($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'TINYINT');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'TINYINT';
        }
        return $this;
    }

    public function smallInteger($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'SMALLINT');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'SMALLINT';
        }
        return $this;
    }

    public function mediumInteger($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'MEDIUMINT');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'MEDIUMINT';
        }
        return $this;
    }

    public function float($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'FLOAT');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'FLOAT';
        }
        return $this;
    }

    public function double($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'DOUBLE');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'DOUBLE';
        }
        return $this;
    }

    public function decimal($name, $total = 8, $places = 2) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'DECIMAL', "$total,$places");
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'DECIMAL';
            $this->bindings['columns'][$name]['length'] = "$total,$places";
        }
        return $this;
    }

    public function boolean($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'BOOLEAN');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'BOOLEAN';
        }
        return $this;
    }

    public function date($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'DATE');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'DATE';
        }
        return $this;
    }

    public function dateTime($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'DATETIME');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'DATETIME';
        }
        return $this;
    }

    public function time($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'TIME');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'TIME';
        }
        return $this;
    }

    public function timestamp($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'TIMESTAMP');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'TIMESTAMP';
        }
        return $this;
    }

    public function binary($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'BINARY');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'BINARY';
        }
        return $this;
    }

    public function uuid($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'UUID');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'UUID';
        }
        return $this;
    }

    public function enum($name, $values) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'ENUM', $values);
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'ENUM';
            $this->bindings['columns'][$name]['values'] = $values;
        }
        return $this;
    }

    public function set($name, $values) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'SET', $values);
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'SET';
            $this->bindings['columns'][$name]['values'] = $values;
        }
        return $this;
    }

    public function json($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'JSON');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'JSON';
        }
        return $this;
    }

    public function jsonb($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'JSONB');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'JSONB';
        }
        return $this;
    }

    public function mediumText($name)
    {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'MEDIUMTEXT');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'MEDIUMTEXT';
        }
        return $this;
    }
    
    public function longText($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'LONGTEXT');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'LONGTEXT';
        }
        return $this;
    }

    public function unsignedBigInteger($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'UNSIGNED BIGINT');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'UNSIGNED BIGINT';
        }
        return $this;
    }

    public function unsignedInteger($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'UNSIGNED INTEGER');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'UNSIGNED INTEGER';
        }
        return $this;
    }

    public function unsignedMediumInteger($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'UNSIGNED MEDIUMINT');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'UNSIGNED MEDIUMINT';
        }
        return $this;
    }

    public function unsignedSmallInteger($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'UNSIGNED SMALLINT');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'UNSIGNED SMALLINT';
        }
        return $this;
    }

    public function unsignedTinyInteger($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'UNSIGNED TINYINT');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'UNSIGNED TINYINT';
        }
        return $this;
    }

    public function unsignedDecimal($name, $total = 8, $places = 2) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'UNSIGNED DECIMAL', "$total,$places");
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'UNSIGNED DECIMAL';
            $this->bindings['columns'][$name]['length'] = "$total,$places";
        }
        return $this;
    }

    public function unsignedFloat($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'UNSIGNED FLOAT');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'UNSIGNED FLOAT';
        }
        return $this;
    }

    public function unsignedDouble($name) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'UNSIGNED DOUBLE');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'UNSIGNED DOUBLE';
        }
        return $this;
    }
    
    public function default($value) {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'DEFAULT', null, $value);
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'DEFAULT';
            $this->bindings['columns'][$name]['default'] = $value;
        }
        return $this;
    }
    
    public function null()
    {
        if (empty($this->bindings['columns'][$name])) {
            $this->bindings['columns'][$name] = $this->addColumn($name, 'NULL');
        } else {
            $this->bindings['columns'][$name]["data_type"] = 'NULL';
        }
        return $this;
    }
    
    public function change()
    {
        foreach ($this->bindings['columns'] as $name => $column) {
            $this->bindings['columns'][$name]['type'] = 'CHANGE';
        }
    }
}