<?php

namespace Hola\Database;

abstract class TableMigration {
    abstract public function up();
    abstract public function down();

    public function runUp()
    {
        try {
            $this->up();
        } catch (\Throwable $e) {
            throw $e;
        }
        return $this;
    }
    
    public function runDown()
    {
        try {
            $this->down();
        } catch (\Throwable $e) {
            throw $e;
        }
        return $this;
    }
    
}