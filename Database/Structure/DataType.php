<?php
namespace Hola\Database\Structure;

class DataType {
    protected $column = null;
    protected $columns = [];
    public AttributeType|null $attributes = null;

    public function getAtributes($name): AttributeType {
        $this->attributes = new AttributeType($name);
        return $this->attributes;
    }

    /* numberic type */
    public function integer($name, $autoIncrement = false): AttributeType 
    {
        $attributes = $this->addColumn($name,'INT');
        if ($autoIncrement) {
            $attributes->autoIncrement()->primaryKey();
        }
        return $attributes;
    }
    public function bigInteger($name, $autoIncrement = false): AttributeType 
    {
        $attributes = $this->addColumn($name,'BIGINT');
        if ($autoIncrement) {
            $attributes->autoIncrement()->primaryKey();
        }
        return $attributes;
    }
    public function smallInteger($name, $length = 6): AttributeType 
    {
        $attributes = $this->addColumn($name,'SMALLINT');
        $attributes->length($length);
        return $attributes;
    }
    
    public function mediumInteger($name, $length = 8): AttributeType 
    {
        $attributes = $this->addColumn($name,'MEDIUMINT');
        $attributes->length($length);
        return $attributes;
    }
    
    public function tinyInteger($name, $length = 1): AttributeType 
    {
        $attributes = $this->addColumn($name,'TINYINT');
        $attributes->length($length);
        return $attributes;
    }
    
    public function decimal($name, $total = 8, $places = 2): AttributeType 
    {
        $attributes = $this->addColumn($name,'DECIMAL');
        $attributes->length("$total,$places");
        return $attributes;
    }
    public function float($name, $total = 8, $places = 2): AttributeType 
    {
        $attributes = $this->addColumn($name,'FLOAT');
        $attributes->length("$total,$places");
        return $attributes;
    }
    public function double($name, $total = 8, $places = 2): AttributeType 
    {
        $attributes = $this->addColumn($name,'DOUBLE');
        $attributes->length("$total,$places");
        return $attributes;
    }
    public function real($name, $total = 8, $places = 2): AttributeType
    {
        $attributes = $this->addColumn($name,'REAL');
        $attributes->length("$total,$places");
        return $attributes;
    }

    public function bit($name): AttributeType
    {
        $attributes = $this->addColumn($name,'BIT');
        return $attributes;
    }
    public function boolean($name): AttributeType
    {
        $attributes = $this->addColumn($name,'BOOLEAN');
        return $attributes;
    }
    public function serial($name): AttributeType
    {
        $attributes = $this->addColumn($name,'SERIAL');
        return $attributes;
    }
    /* end numberic type */

    /* date time type */
    public function date($name): AttributeType
    {
        $attributes = $this->addColumn($name,'DATE');
        return $attributes;
    }
    public function dateTime($name) {
        $attributes = $this->addColumn($name,'DATETIME');
        return $attributes;
    }
    public function time($name): AttributeType
    {
        $attributes = $this->addColumn($name,'TIME');
        return $attributes;
    }
    
    public function timestamp($name): AttributeType
    {
        $attributes = $this->addColumn($name,'TIMESTAMP');
        return $attributes;
    }
    
    public function year($name): AttributeType
    {
        $attributes = $this->addColumn($name,'YEAR');
        return $attributes;
    }
    /* end date time type */

    /* string type */
    public function char($name, $length = 255): AttributeType
    {
        return $this->addColumn($name, 'CHAR')->length($length);
    }
    
    public function varchar($name, $length = 255): AttributeType
    {
        return $this->addColumn($name, 'VARCHAR')->length($length);
    }
    
    public function tinyText($name): AttributeType
    {
        return $this->addColumn($name, 'TINYTEXT');
    }
    public function text($name): AttributeType
    {
        return $this->addColumn($name,'TEXT');
    }
    
    public function mediumText($name): AttributeType
    {
        return $this->addColumn($name,'MEDIUMTEXT');
    }
    
    public function longText($name): AttributeType
    {
        return $this->addColumn($name,'LONGTEXT');
    }

    public function binary($name): AttributeType
    {
        return $this->addColumn($name,'BINARY');
    }
    
    public function varbinary($name, $length = 255): AttributeType
    {
        return $this->addColumn($name, 'VARBINARY')->length($length);
    }

    public function tinyBlob($name): AttributeType
    {
        return $this->addColumn($name,'TINYBLOB');
    }
    
    public function blob($name): AttributeType
    {
        return $this->addColumn($name,'BLOB');
    }
    
    public function mediumBlob($name): AttributeType
    {
        return $this->addColumn($name,'MEDIUMBLOB');
    }
    
    public function longBlob($name): AttributeType
    {
        return $this->addColumn($name,'LONGBLOB');
    }

    public function enum($name, $values): AttributeType
    {
        return $this->addColumn($name,'ENUM')->values($values);
    }
    
    public function set($name, $values): AttributeType
    {
        return $this->addColumn($name,'SET')->values($values);
    }
    /* end string type */

    /* spatial */
    public function geometry($name): AttributeType
    {
        return $this->addColumn($name,'GEOMETRY');
    }
    
    public function point($name): AttributeType
    {
        return $this->addColumn($name,'POINT');
    }
    
    public function linestring($name): AttributeType
    {
        return $this->addColumn($name,'LINESTRING');
    }
    
    public function polygon($name): AttributeType
    {
        return $this->addColumn($name,'POLYGON');
    }
    
    public function multipoint($name): AttributeType
    {
        return $this->addColumn($name,'MULTIPOINT');
    }
    
    public function multilinestring($name): AttributeType
    {
        return $this->addColumn($name,'MULTILINESTRING');
    }
    
    public function multipolygon($name): AttributeType
    {
        return $this->addColumn($name,'MULTIPOLYGON');
    }
    
    public function geometrycollection($name): AttributeType
    {
        return $this->addColumn($name,'GEOMETRYCOLLECTION');
    }
    /* end spatial */

    /* json type */
    public function json($name): AttributeType
    {
        return $this->addColumn($name,'JSON');
    }
    
    public function jsonb($name): AttributeType
    {
        return $this->addColumn($name,'JSONB');
    }
    /* end json type */
    
    /* other type */
    public function unsignedBigInteger($name): AttributeType
    {
        return $this->addColumn($name,'UNSIGNED BIGINT');
    }
    
    public function unsignedInteger($name): AttributeType
    {
        return $this->addColumn($name,'UNSIGNED INT');
    }
    
    public function unsignedMediumInteger($name): AttributeType
    {
        return $this->addColumn($name,'UNSIGNED MEDIUMINT');
    }

    public function unsignedSmallInteger($name): AttributeType
    {
        return $this->addColumn($name,'UNSIGNED SMALLINT');
    }

    public function unsignedTinyInteger($name): AttributeType
    {
        return $this->addColumn($name,'UNSIGNED TINYINT');
    }

    public function unsignedDecimal($name, $total = 8, $places = 2): AttributeType
    {
        return $this->addColumn($name,'UNSIGNED DECIMAL')->length("$total,$places");
    }

    public function unsignedFloat($name, $total = 8, $places = 2): AttributeType
    {
        return $this->addColumn($name,'UNSIGNED FLOAT')->length("$total,$places");
    }

    public function unsignedDouble($name, $total = 8, $places = 2) {
        return $this->addColumn($name,'UNSIGNED DOUBLE')->length("$total,$places");
    }
}