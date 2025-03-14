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
        $attributes = $this->resolveColumn($name,'INT');
        if ($autoIncrement) {
            $attributes->autoIncrement()->primaryKey();
        }
        return $attributes;
    }

    public function bigInteger($name, $autoIncrement = false): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resolveColumn($name,'BIGINT');
        if ($autoIncrement) {
            $attributes->autoIncrement()->primaryKey();
        }
        return $attributes;
    }

    public function smallInteger($name, $length = 6): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resolveColumn($name,'SMALLINT');
        $attributes->length($length);
        return $attributes;
    }
    
    public function mediumInteger($name, $length = 8): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resolveColumn($name,'MEDIUMINT');
        $attributes->length($length);
        return $attributes;
    }
    
    public function tinyInteger($name, $length = 1): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resolveColumn($name,'TINYINT');
        $attributes->length($length);
        return $attributes;
    }
    
    public function decimal($name, $total = 8, $places = 2): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resolveColumn($name,'DECIMAL');
        $attributes->length("$total,$places");
        return $attributes;
    }

    public function float($name, $total = 8, $places = 2): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resolveColumn($name,'FLOAT');
        $attributes->length("$total,$places");
        return $attributes;
    }

    public function double($name, $total = 8, $places = 2): AttributeType 
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resolveColumn($name,'DOUBLE');
        $attributes->length("$total,$places");
        return $attributes;
    }

    public function real($name, $total = 8, $places = 2): AttributeType
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resolveColumn($name,'REAL');
        $attributes->length("$total,$places");
        return $attributes;
    }

    public function bit($name): AttributeType
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resolveColumn($name,'BIT');
        return $attributes;
    }

    public function boolean($name): AttributeType
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resolveColumn($name,'BOOLEAN');
        return $attributes;
    }

    public function serial($name): AttributeType
    {
        /* @var AttributeType $attributes */
        $attributes = $this->resolveColumn($name,'SERIAL');
        return $attributes;
    }
    /* end numberic type */

    /* date time type */
    public function date($name): AttributeType
    {
        $attributes = $this->resolveColumn($name,'DATE');
        return $attributes;
    }

    public function dateTime($name): AttributeType {
        $attributes = $this->resolveColumn($name,'DATETIME');
        return $attributes;
    }

    public function time($name): AttributeType
    {
        $attributes = $this->resolveColumn($name,'TIME');
        return $attributes;
    }

    public function timestamp($name): AttributeType
    {
        $attributes = $this->resolveColumn($name,'TIMESTAMP');
        return $attributes;
    }
    
    public function year($name): AttributeType
    {
        $attributes = $this->resolveColumn($name,'YEAR');
        return $attributes;
    }
    /* end date time type */

    /* string type */
    public function char($name, $length = 255): AttributeType
    {
        return $this->resolveColumn($name, 'CHAR')->length($length);
    }
    
    public function varchar($name, $length = 255): AttributeType
    {
        return $this->resolveColumn($name, 'VARCHAR')->length($length);
    }
    
    public function tinyText($name): AttributeType
    {
        return $this->resolveColumn($name, 'TINYTEXT');
    }

    public function text($name): AttributeType
    {
        return $this->resolveColumn($name,'TEXT');
    }
    
    public function mediumText($name): AttributeType
    {
        return $this->resolveColumn($name,'MEDIUMTEXT');
    }
    
    public function longText($name): AttributeType
    {
        return $this->resolveColumn($name,'LONGTEXT');
    }

    public function binary($name): AttributeType
    {
        return $this->resolveColumn($name,'BINARY');
    }
    
    public function varbinary($name, $length = 255): AttributeType
    {
        return $this->resolveColumn($name, 'VARBINARY')->length($length);
    }

    public function tinyBlob($name): AttributeType
    {
        return $this->resolveColumn($name,'TINYBLOB');
    }
    
    public function blob($name): AttributeType
    {
        return $this->resolveColumn($name,'BLOB');
    }
    
    public function mediumBlob($name): AttributeType
    {
        return $this->resolveColumn($name,'MEDIUMBLOB');
    }
    
    public function longBlob($name): AttributeType
    {
        return $this->resolveColumn($name,'LONGBLOB');
    }

    public function enum($name, $values): AttributeType
    {
        return $this->resolveColumn($name,'ENUM')->values($values);
    }
    
    public function set($name, $values): AttributeType
    {
        return $this->resolveColumn($name,'SET')->values($values);
    }
    /* end string type */

    /* spatial */
    public function geometry($name): AttributeType
    {
        return $this->resolveColumn($name,'GEOMETRY');
    }
    
    public function point($name): AttributeType
    {
        return $this->resolveColumn($name,'POINT');
    }
    
    public function linestring($name): AttributeType
    {
        return $this->resolveColumn($name,'LINESTRING');
    }
    
    public function polygon($name): AttributeType
    {
        return $this->resolveColumn($name,'POLYGON');
    }
    
    public function multipoint($name): AttributeType
    {
        return $this->resolveColumn($name,'MULTIPOINT');
    }
    
    public function multilinestring($name): AttributeType
    {
        return $this->resolveColumn($name,'MULTILINESTRING');
    }
    
    public function multipolygon($name): AttributeType
    {
        return $this->resolveColumn($name,'MULTIPOLYGON');
    }
    
    public function geometrycollection($name): AttributeType
    {
        return $this->resolveColumn($name,'GEOMETRYCOLLECTION');
    }
    /* end spatial */

    /* json type */
    public function json($name): AttributeType
    {
        return $this->resolveColumn($name,'JSON');
    }
    
    public function jsonb($name): AttributeType
    {
        return $this->resolveColumn($name,'JSONB');
    }
    /* end json type */
    
    /* other type */
    public function unsignedBigInteger($name): AttributeType
    {
        return $this->resolveColumn($name,'UNSIGNED BIGINT');
    }
    
    public function unsignedInteger($name): AttributeType
    {
        return $this->resolveColumn($name,'UNSIGNED INT');
    }
    
    public function unsignedMediumInteger($name): AttributeType
    {
        return $this->resolveColumn($name,'UNSIGNED MEDIUMINT');
    }

    public function unsignedSmallInteger($name): AttributeType
    {
        return $this->resolveColumn($name,'UNSIGNED SMALLINT');
    }

    public function unsignedTinyInteger($name): AttributeType
    {
        return $this->resolveColumn($name,'UNSIGNED TINYINT');
    }

    public function unsignedDecimal($name, $total = 8, $places = 2): AttributeType
    {
        return $this->resolveColumn($name,'UNSIGNED DECIMAL')->length("$total,$places");
    }

    public function unsignedFloat($name, $total = 8, $places = 2): AttributeType
    {
        return $this->resolveColumn($name,'UNSIGNED FLOAT')->length("$total,$places");
    }

    public function unsignedDouble($name, $total = 8, $places = 2): AttributeType {
        return $this->resolveColumn($name,'UNSIGNED DOUBLE')->length("$total,$places");
    }
    
    public function dropColumn($name)
    {
        return $this->resolveColumn($name,'')->drop();
    }
}