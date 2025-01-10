<?php

namespace Hola\Database;

class DBO extends Model {
    public static function query($sql) {
        return self::init()->query($sql);
    }
}