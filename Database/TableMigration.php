<?php

namespace Hola\Database;

class TableStructure extends TableBuilder {
    public function primaryKey($name = '')
    {
        if (empty($name)) {
            $selected = $this->bindings['selected_column'];
            $this->bindings['columns'][$selected]["primary_key"] = true;
        } else {
            if (!empty($this->bindings['columns'][$name])) {
                $this->bindings['columns'][$name]["primary_key"] = true;
            } else {
                throw new \Exception("Column $name not found");
            }
        }
        return $this;
    }

    public function unique($name = null)
    {
        if (empty($name)) {
            $selected = $this->bindings['selected_column'];
            $this->bindings['columns'][$selected]["unique"] = true;
        } else {
            if (!empty($this->bindings['columns'][$name])) {
                $this->bindings['columns'][$name]["unique"] = true;
            } else {
                throw new \Exception("Column $name not found");
            }
        }
        return $this;
    }
    
    public function index($name = '', $column = null)
    {
        $idx_name = !empty($name) ? $name : "idx_{$column}";
        if (empty($column)) {
            $selected = $this->bindings['selected_column'];
            $this->bindings['columns'][$selected]["index"] = [
                'name' => $idx_name,
                'type' => 'INDEX'
            ];
        } else {
            if (!empty($this->bindings['columns'][$column])) {
                $this->bindings['columns'][$column]["index"] = [
                    'name' => $idx_name,
                    'type' => 'INDEX'
                ];
            } else {
                throw new \Exception("Column $column not found");
            }
        }
        return $this;
    }
    
    public function spatialIndex($name = '', $column = null)
    {
        $idx_name = !empty($name) ? $name : "idx_{$column}";
        if (empty($column)) {
            $selected = $this->bindings['selected_column'];
            $this->bindings['columns'][$selected]["index"] = [
                'name' => $idx_name,
                'type' => 'SPATIAL INDEX'
            ];
        } else {
            if (!empty($this->bindings['columns'][$column])) {
                $this->bindings['columns'][$column]["index"] = [
                    'name' => $idx_name,
                    'type' => 'SPATIAL INDEX'
                ];
            } else {
                throw new \Exception("Column $column not found");
            }
        }
        return $this;
    }
    
    public function fullTextIndex($name = '', $column = null)
    {
        $idx_name = !empty($name) ? $name : "idx_{$column}";
        if (empty($column)) {
            $selected = $this->bindings['selected_column'];
            $this->bindings['columns'][$selected]["index"] = [
                'name' => $idx_name,
                'type' => 'FULLTEXT INDEX'
            ];
        } else {
            if (!empty($this->bindings['columns'][$column])) {
                $this->bindings['columns'][$column]["index"] = [
                    'name' => $idx_name,
                    'type' => 'FULLTEXT INDEX'
                ];
            } else {
                throw new \Exception("Column $column not found");
            }
        }
        return $this;
    }
    
    public function foreign($name = null)
    {
        if (empty($name)) {
            $selected = $this->bindings['selected_column'];
            $this->bindings['columns'][$selected]["foreign_key"] = true;
        } else {
            if (!empty($this->bindings['columns'][$name])) {
                $this->bindings['columns'][$name]["foreign_key"] = true;
            } else {
                throw new \Exception("Column $name not found");
            }
        }
        return $this;
    }
    
    public function comment($comment, $name = null)
    {
        if (empty($name)) {
            $selected = $this->bindings['selected_column'];
            $this->bindings['columns'][$selected]["comment"] = $comment;
        } else {
            if (!empty($this->bindings['columns'][$name])) {
                $this->bindings['columns'][$name]["comment"] = $comment;
            } else {
                throw new \Exception("Column $name not found");
            }
        }
        return $this;
    }
    
    public function null($name = null)
    {
        if (empty($name)) {
            $selected = $this->bindings['selected_column'];
            $this->bindings['columns'][$selected]["nullable"] = true;
        } else {
            if (!empty($this->bindings['columns'][$name])) {
                $this->bindings['columns'][$name]["nullable"] = true;
            } else {
                throw new \Exception("Column $name not found");
            }
        }
        return $this;
    }
    
    public function default($value, $name = null)
    {
        if (empty($name)) {
            $selected = $this->bindings['selected_column'];
            $this->bindings['columns'][$selected]["default"] = $value;
        } else {
            if (!empty($this->bindings['columns'][$name])) {
                $this->bindings['columns'][$name]["default"] = $value;
            } else {
                throw new \Exception("Column $name not found");
            }
        }
        return $this;
    }
    
    public function autoIncrement($name = null)
    {
        if (empty($name)) {
            $selected = $this->bindings['selected_column'];
            $this->bindings['columns'][$selected]["auto_increment"] = true;
        } else {
            if (!empty($this->bindings['columns'][$name])) {
                $this->bindings['columns'][$name]["auto_increment"] = true;
            } else {
                throw new \Exception("Column $name not found");
            }
        }
        return $this;
    }
    
    public function unsigned($name = null)
    {
        if (empty($name)) {
            $selected = $this->bindings['selected_column'];
            $this->bindings['columns'][$selected]["unsigned"] = true;
        } else {
            if (!empty($this->bindings['columns'][$name])) {
                $this->bindings['columns'][$name]["unsigned"] = true;
            } else {
                throw new \Exception("Column $name not found");
            }
        }
        return $this;
    }
    
   /* numberic type */
    public function tinyInteger($name, $length = 1) {
        return $this->addColumn($name,'TINYINT')
            ->resloveBound(['length' => $length]);
    }
    public function smallInteger($name, $length = 6) {
        return $this->addColumn($name,'SMALLINT')
            ->resloveBound($name, ['length' => $length]);
    }
    public function mediumInteger($name, $length = 8) {
        return $this->addColumn($name, 'MEDIUMINT')
            ->resloveBound($name, ['length' => $length]);
    }
    public function integer($name, $autoIncrement = false, $primaryKey = false) {
        return $this->addColumn($name, 'INTEGER')
            ->resloveBound($name, [
                'auto_increment' => $autoIncrement,
                'primary_key' => $primaryKey
            ]);
    }
    public function bigInteger($name, $autoIncrement = false, $primaryKey = false) {
        return $this->addColumn($name, 'BIGINT')
            ->resloveBound($name, [
                'auto_increment' => $autoIncrement,
                'primary_key' => $primaryKey
            ]);
    }
    public function decimal($name, $total = 8, $places = 2) {
        return $this->addColumn($name, 'DECIMAL')
            ->resloveBound($name, [
                'length' => "$total,$places"
            ]);
    }
    public function float($name, $total = 8, $places = 2) {
        return $this->addColumn($name,'FLOAT')
            ->resloveBound($name, ['length' => "$total,$places"]);
    }
    public function double($name, $total = 8, $places = 2) {
        return $this->addColumn($name,'DOUBLE')
            ->resloveBound($name, ['length' => "$total,$places"]);
    }
    public function real($name, $total = 8, $places = 2)
    {
        return $this->addColumn($name, 'REAL')
            ->resloveBound($name, ['length' => "$total,$places"]);
    }

    public function bit($name)
    {
        return $this->addColumn($name, 'BIT');
    }
    public function boolean($name) {
        return $this->addColumn($name, 'BOOLEAN');
    }
    public function serial($name)
    {
        return $this->addColumn($name, 'SERIAL');
    }

    /* end numberic type */

    /* date time type */
    public function date($name) {
        return $this->addColumn($name, 'DATE');
    }
    public function dateTime($name) {
        return $this->addColumn($name,'DATETIME');
    }
    public function time($name) {
        return $this->addColumn($name,'TIME');
    }
    public function timestamp($name) {
        return $this->addColumn($name,'TIMESTAMP');
    }
    public function year($name) {
        return $this->addColumn($name,'YEAR');
    }
    /* end date time type */

    /* string type */
    public function char($name, $length = 255) {
        return $this->addColumn($name, 'CHAR')
            ->resloveBound($name, ['length' => $length]);
    }
    public function varchar($name, $length = 255) {
        return $this->addColumn($name, 'VARCHAR')
            ->resloveBound($name, ['length' => $length]);
    }
    public function tinyText($name) {
        return $this->addColumn($name, 'TINYTEXT');
    }
    public function text($name) {
        return $this->addColumn($name,'TEXT');
    }
    public function mediumText($name) {
        return $this->addColumn($name,'MEDIUMTEXT');
    }
    public function longText($name) {
        return $this->addColumn($name,'LONGTEXT');
    }

    public function binary($name) {
        return $this->addColumn($name,'BINARY');
    }
    public function varbinary($name, $length = 255) {
        return $this->addColumn($name, 'VARBINARY')
            ->resloveBound($name, ['length' => $length]);
    }

    public function tinyBlob($name) {
        return $this->addColumn($name,'TINYBLOB');
    }
    public function blob($name) {
        return $this->addColumn($name,'BLOB');
    }
    public function mediumBlob($name) {
        return $this->addColumn($name,'MEDIUMBLOB');
    }
    public function longBlob($name) {
        return $this->addColumn($name,'LONGBLOB');
    }

    public function enum($name, $values) {
        return $this->addColumn($name,'ENUM')
            ->resloveBound($name, ['values' => $values]);
    }
    public function set($name, $values) {
        return $this->addColumn($name,'SET')
            ->resloveBound($name, ['values' => $values]);
    }

    /* end string type */

    /* spatial */
    public function geometry($name) {
        return $this->addColumn($name,'GEOMETRY');
    }
    public function point($name) {
        return $this->addColumn($name,'POINT');
    }
    public function linestring($name) {
        return $this->addColumn($name,'LINESTRING');
    }
    public function polygon($name) {
        return $this->addColumn($name,'POLYGON');
    }
    public function multipoint($name) {
        return $this->addColumn($name,'MULTIPOINT');
    }
    public function multilinestring($name) {
        return $this->addColumn($name,'MULTILINESTRING');
    }
    public function multipolygon($name) {
        return $this->addColumn($name,'MULTIPOLYGON');
    }
    public function geometrycollection($name) {
        return $this->addColumn($name,'GEOMETRYCOLLECTION');
    }
    /* end spatial */

    /* json type */
    public function json($name) {
        return $this->addColumn($name,'JSON');
    }
    public function jsonb($name) {
        return $this->addColumn($name,'JSONB');
    }
    /* end json type */
    
    /* unsigned type */
    public function unsignedBigInteger($name) {
        return $this->addColumn($name,'UNSIGNED BIGINT');
    }
    public function unsignedInteger($name) {
        return $this->addColumn($name,'UNSIGNED INTEGER');
    }
    public function unsignedMediumInteger($name) {
        return $this->addColumn($name,'UNSIGNED MEDIUMINT');
    }

    public function unsignedSmallInteger($name) {
        return $this->addColumn($name,'UNSIGNED SMALLINT');
    }

    public function unsignedTinyInteger($name) {
        return $this->addColumn($name,'UNSIGNED TINYINT');
    }

    public function unsignedDecimal($name, $total = 8, $places = 2) {
        return $this->addColumn($name,'UNSIGNED DECIMAL')
            ->resloveBound($name, ['length' => "$total,$places"]);
    }

    public function unsignedFloat($name, $total = 8, $places = 2) {
        return $this->addColumn($name,'UNSIGNED FLOAT')
            ->resloveBound($name, ['length' => "$total,$places"]);
    }

    public function unsignedDouble($name, $total = 8, $places = 2) {
        return $this->addColumn($name,'UNSIGNED DOUBLE')
            ->resloveBound($name, ['length' => "$total,$places"]);
    }

    public function change()
    {
        $selected = $this->bindings['selected_column'];
        $this->bindings['columns'][$selected]['change'] = true;
    }
}