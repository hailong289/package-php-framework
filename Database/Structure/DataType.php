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
        /* @var AttributeType $attributes */
        $attributes = $this->resloveColumn($name,'INT');
        if ($autoIncrement) {
            $attributes->autoIncrement()->primaryKey();
        }
        return $attributes;
    }

    public function bigInteger($name, $autoIncrement = false): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resloveColumn($name,'BIGINT');
        if ($autoIncrement) {
            $attributes->autoIncrement()->primaryKey();
        }
        return $attributes;
    }

    public function smallInteger($name, $length = 6): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resloveColumn($name,'SMALLINT');
        $attributes->length($length);
        return $attributes;
    }
    
    public function mediumInteger($name, $length = 8): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resloveColumn($name,'MEDIUMINT');
        $attributes->length($length);
        return $attributes;
    }
    
    public function tinyInteger($name, $length = 1): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resloveColumn($name,'TINYINT');
        $attributes->length($length);
        return $attributes;
    }
    
    public function decimal($name, $total = 8, $places = 2): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resloveColumn($name,'DECIMAL');
        $attributes->length("$total,$places");
        return $attributes;
    }

    public function float($name, $total = 8, $places = 2): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resloveColumn($name,'FLOAT');
        $attributes->length("$total,$places");
        return $attributes;
    }

    public function double($name, $total = 8, $places = 2): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resloveColumn($name,'DOUBLE');
        $attributes->length("$total,$places");
        return $attributes;
    }

    public function real($name, $total = 8, $places = 2): AttributeType
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resloveColumn($name,'REAL');
        $attributes->length("$total,$places");
        return $attributes;
    }

    public function bit($name): AttributeType
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resloveColumn($name,'BIT');
        return $attributes;
    }

    public function boolean($name): AttributeType
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resloveColumn($name,'BOOLEAN');
        return $attributes;
    }

    public function serial($name): AttributeType
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resloveColumn($name,'SERIAL');
        return $attributes;
    }
    /* end numberic type */

    /* date time type */
    public function date($name): AttributeType
    {
        $attributes = $this->resloveColumn($name,'DATE');
        return $attributes;
    }

    public function dateTime($name): AttributeType {
        $attributes = $this->resloveColumn($name,'DATETIME');
        return $attributes;
    }

    public function time($name): AttributeType
    {
        $attributes = $this->resloveColumn($name,'TIME');
        return $attributes;
    }

    public function timestamp($name): AttributeType
    {
        $attributes = $this->resloveColumn($name,'TIMESTAMP');
        return $attributes;
    }
    
    public function year($name): AttributeType
    {
        $attributes = $this->resloveColumn($name,'YEAR');
        return $attributes;
    }
    /* end date time type */

    /* string type */
    public function char($name, $length = 255): AttributeType
    {
        return $this->resloveColumn($name, 'CHAR')->length($length);
    }
    
    public function varchar($name, $length = 255): AttributeType
    {
        return $this->resloveColumn($name, 'VARCHAR')->length($length);
    }
    
    public function tinyText($name): AttributeType
    {
        return $this->resloveColumn($name, 'TINYTEXT');
    }
    public function text($name): AttributeType
    {
        return $this->resloveColumn($name,'TEXT');
    }
    
    public function mediumText($name): AttributeType
    {
        return $this->resloveColumn($name,'MEDIUMTEXT');
    }
    
    public function longText($name): AttributeType
    {
        return $this->resloveColumn($name,'LONGTEXT');
    }

    public function binary($name): AttributeType
    {
        return $this->resloveColumn($name,'BINARY');
    }
    
    public function varbinary($name, $length = 255): AttributeType
    {
        return $this->resloveColumn($name, 'VARBINARY')->length($length);
    }

    public function tinyBlob($name): AttributeType
    {
        return $this->resloveColumn($name,'TINYBLOB');
    }
    
    public function blob($name): AttributeType
    {
        return $this->resloveColumn($name,'BLOB');
    }
    
    public function mediumBlob($name): AttributeType
    {
        return $this->resloveColumn($name,'MEDIUMBLOB');
    }
    
    public function longBlob($name): AttributeType
    {
        return $this->resloveColumn($name,'LONGBLOB');
    }

    public function enum($name, $values): AttributeType
    {
        return $this->resloveColumn($name,'ENUM')->values($values);
    }
    
    public function set($name, $values): AttributeType
    {
        return $this->resloveColumn($name,'SET')->values($values);
    }
    /* end string type */

    /* spatial */
    public function geometry($name): AttributeType
    {
        return $this->resloveColumn($name,'GEOMETRY');
    }
    
    public function point($name): AttributeType
    {
        return $this->resloveColumn($name,'POINT');
    }
    
    public function linestring($name): AttributeType
    {
        return $this->resloveColumn($name,'LINESTRING');
    }
    
    public function polygon($name): AttributeType
    {
        return $this->resloveColumn($name,'POLYGON');
    }
    
    public function multipoint($name): AttributeType
    {
        return $this->resloveColumn($name,'MULTIPOINT');
    }
    
    public function multilinestring($name): AttributeType
    {
        return $this->resloveColumn($name,'MULTILINESTRING');
    }
    
    public function multipolygon($name): AttributeType
    {
        return $this->resloveColumn($name,'MULTIPOLYGON');
    }
    
    public function geometrycollection($name): AttributeType
    {
        return $this->resloveColumn($name,'GEOMETRYCOLLECTION');
    }
    /* end spatial */

    /* json type */
    public function json($name): AttributeType
    {
        return $this->resloveColumn($name,'JSON');
    }
    
    public function jsonb($name): AttributeType
    {
        return $this->resloveColumn($name,'JSONB');
    }
    /* end json type */
    
    /* other type */
    public function unsignedBigInteger($name): AttributeType
    {
        return $this->resloveColumn($name,'UNSIGNED BIGINT');
    }
    
    public function unsignedInteger($name): AttributeType
    {
        return $this->resloveColumn($name,'UNSIGNED INT');
    }
    
    public function unsignedMediumInteger($name): AttributeType
    {
        return $this->resloveColumn($name,'UNSIGNED MEDIUMINT');
    }

    public function unsignedSmallInteger($name): AttributeType
    {
        return $this->resloveColumn($name,'UNSIGNED SMALLINT');
    }

    public function unsignedTinyInteger($name): AttributeType
    {
        return $this->resloveColumn($name,'UNSIGNED TINYINT');
    }

    public function unsignedDecimal($name, $total = 8, $places = 2): AttributeType
    {
        return $this->resloveColumn($name,'UNSIGNED DECIMAL')->length("$total,$places");
    }

    public function unsignedFloat($name, $total = 8, $places = 2): AttributeType
    {
        return $this->resloveColumn($name,'UNSIGNED FLOAT')->length("$total,$places");
    }

    public function unsignedDouble($name, $total = 8, $places = 2): AttributeType {
        return $this->resloveColumn($name,'UNSIGNED DOUBLE')->length("$total,$places");
    }
    
    public function dropColumn($name)
    {
        return $this->resloveColumn($name,'')->drop();
    }
}