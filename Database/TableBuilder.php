<?php

namespace Hola\Database;

class TableBuilder {
    protected $TYPE_CREATE = 'CREATE';
    protected $TYPE_USE = 'USE';
    protected $TYPE_DROP = 'DROP';
    protected $TYPE_DROP_EXISTS = 'DROP_EXISTS';
    protected $TYPE_RENAME = 'RENAME';
    protected $TYPE_HAS = 'HAS';
    protected $TYPE_ADD = 'ADD';
    protected $TYPE_MODIFY = 'MODIFY';
    protected static Connection|null $connection;

    public $bindings = [
        'table' => null,
        'new_table' => null,
        'type' => [],
        'columns' => [],
    ];

    public function create($table, $callback) {
        $this->bindings['type'][] = $this->TYPE_CREATE;
        $this->bindings['table'] = $table;
        $callback($this);
        return $this->run();
    }

    public function useTable($table, $callback) {
        $this->bindings['type'][] = $this->TYPE_USE;
        $this->bindings['table'] = $table;
        $callback($this);
        return $this->run();
    }

    public function drop($table) {
        $this->bindings['type'][] = $this->TYPE_DROP;
        $this->bindings['table'] = $table;
        return $this->run();
    }

    public function dropIfExists($table) {
        $this->bindings['type'][] = $this->TYPE_DROP_EXISTS;
        $this->bindings['table'] = $table;
        return $this->run();
    }

    public function rename($table, $newName) {
        $this->bindings['type'][] = $this->TYPE_RENAME;
        $this->bindings['table'] = $table;
        $this->bindings['new_table'] = $newName;
        return $this->run();
    }

    public function hasTable($table) {
        $this->bindings['type'][] = $this->TYPE_HAS;
        $this->bindings['table'] = $table;
        return $this->run();
    }

    public function hasColumn($column) {
        $this->bindings['columns'][$column] = [
            'table' => $this->bindings['table'],
            'name' => $column,
            'type' => 'has'
        ];
        return $this;
    }

    public function addColumn(
        $column,
        $type,
        $length = null,
        $default = null,
        $nullable = false,
        $comment = null,
        $autoIncrement = false,
        $primaryKey = false,
        $unique = false,
        $index = false,
        $foreignKey = null
    ) {
        $this->bindings['columns'][$column] = [
            'table' => $this->bindings['table'],
            'name' => $column,
            'type' => 'add',
            'data_type' => $type,
            'length' => $length,
            'default' => $default,
            'nullable' => $nullable,
            'comment' => $comment,
            'auto_increment' => $autoIncrement,
            'primary_key' => $primaryKey,
            'unique' => $unique,
            'index' => $index,
            'foreign_key' => $foreignKey
        ];
        return $this;
    }

    public function dropColumn($column) {
        $this->bindings['drop']['column'][$column] = [
            'table' => $this->bindings['table'],
            'name' => $column,
            'type' => 'drop'
        ];
        return $this;
    }

    public function modifyColumn(
        $column,
        $type,
        $length = null,
        $default = null,
        $nullable = false,
        $comment = null,
        $autoIncrement = false,
        $primaryKey = false,
        $unique = false,
        $index = false,
        $foreignKey = null
    ) {
        $this->bindings['columns'][$column] = [
            'table' => $this->bindings['table'],
            'name' => $column,
            'type' => 'modify',
            'data_type' => $type,
            'length' => $length,
            'default' => $default,
            'nullable' => $nullable,
            'comment' => $comment,
            'auto_increment' => $autoIncrement,
            'primary_key' => $primaryKey,
            'unique' => $unique,
            'index' => $index,
            'foreign_key' => $foreignKey
        ];
        return $this;
    }

    public function renameColumn($column, $newName) {
        $this->bindings['columns'][$column] = [
            'table' => $this->bindings['table'],
            'name' => $column,
            'type' => 'rename',
            'data_type' => $type,
            'length' => $length,
            'default' => $default,
            'nullable' => $nullable,
            'comment' => $comment,
            'auto_increment' => $autoIncrement,
            'primary_key' => $primaryKey,
            'unique' => $unique,
            'index' => $index,
            'foreign_key' => $foreignKey
        ];
        return $this;
    }

    public function addIndex($column, $indexName = null) {
        $this->bindings['columns'][] = [
            'table' => $this->bindings['table'],
            'type' => 'add_index',
            'name' => $column,
            'index_name' => $indexName
        ];
        return $this;
    }

    public function dropIndex($name) {
        $this->bindings['drop']['index'][] = [
            'table' => $this->bindings['table'],
            'type' => 'drop_index',
            'index_name' => $name
        ];
        return $this;
    }

    private function toSql()
    {
        $sql = '';
        $sql = $this->resloveTable($sql);
        $this->bindings = [
            'table' => null,
            'new_table' => null,
            'type' => [],
            'columns' => [],
        ];
        return $sql;
    }

    public static function connection()
    {
        if (is_null(self::$connection)) {
            self::$connection = new Connection();
        }
        return self::$connection;
    }

    private function run()
    {
        $status = false;
        $sql = $this->toSql();
        $connection = self::connection();
        $connection->beginTransaction();
        try {
            $status = $connection->query($sql);
            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }
        return $status;
    }

    private function resloveTable($sql)
    {
        foreach ($this->bindings['type'] as $type) {
            switch ($type) {
                case $this->TYPE_CREATE:
                    $sql .= $this->resloveCreate();
                    break;
                case $this->TYPE_USE:
                    $sql .= $this->resloveUse();
                    break;
                case $this->TYPE_DROP:
                    $sql .= $this->resloveDrop();
                    break;
                case $this->TYPE_DROP_EXISTS:
                    $sql .= $this->resloveDropExists();
                    break;
                case $this->TYPE_RENAME:
                    $sql .= $this->resloveRename();
                    break;
                case $this->TYPE_HAS:
                    $sql .= $this->resloveHas();
                    break;
            }
        }
        return $sql;
    }

    private function resloveCreate($sql)
    {
        $sql .= "CREATE TABLE {$this->bindings['table']};\n";
        $sql .= $this->resloveColumn($sql);
        return $sql;
    }

    private function resloveUse($sql)
    {
        $sql .= $this->resloveColumn($sql);
        return $sql;
    }

    private function resloveDrop($sql)
    {
        $sql .= "DROP TABLE {$this->bindings['table']};\n";
    }

    private function resloveDropExists($sql)
    {
        $sql .= "DROP TABLE IF EXISTS {$this->bindings['table']};\n";
    }

    private function resloveRename($sql)
    {
        $sql .= "RENAME TABLE {$this->bindings['table']} TO {$this->bindings['new_table']};\n";
    }

    private function resloveHas($sql)
    {
        $sql .= "SHOW TABLES LIKE '{$this->bindings['table']}';\n";
    }

    private function resloveColumn($sql)
    {
        foreach ($this->bindings['columns'] as $column) {
            switch ($column['type']) {
                case 'add':
                    $sql .= $this->resloveAddColumn($sql, $column);
                    break;
                case 'drop':
                    $sql .= $this->resloveDropColumn($sql, $column);
                    break;
                case 'modify':
                    $sql .= $this->resloveModifyColumn($sql, $column);
                    break;
                case 'rename':
                    $sql .= $this->resloveRenameColumn($sql, $column);
                    break;
                case 'add_index':
                    $sql .= $this->resloveAddIndex($sql, $column);
                    break;
                case 'drop_index':
                    $sql .= $this->resloveDropIndex($sql, $column);
                    break;
            }
        }
    }

    private function resloveAddColumn($sql, $column)
    {
        $sql .= "ALTER TABLE {$this->bindings['table']} ADD COLUMN {$column['name']} {$column['data_type']}";
        if ($column['length']) {
            $sql .= "({$column['length']})";
        }
        if ($column['default']) {
            $sql .= " DEFAULT {$column['default']}";
        }
        if ($column['nullable']) {
            $sql .= " NULL";
        } else {
            $sql .= " NOT NULL";
        }

        if ($column['auto_increment']) {
            $sql .= " AUTO_INCREMENT";
        }

        if ($column['primary_key']) {
            $sql .= " PRIMARY KEY";
        }

        if ($column['unique']) {
            $sql .= " UNIQUE";
        }

        if ($column['index']) {
            $sql .= " INDEX";
        }

        if ($column['foreign_key']) {
            $sql .= " FOREIGN KEY";
        }

        if ($column['comment']) {
            $sql .= " COMMENT '{$column['comment']}'";
        }

        $sql .= ";\n";
        return $sql;
    }

    private function resloveModifyColumn($sql, $column)
    {
        $sql .= "ALTER TABLE {$this->bindings['table']} MODIFY COLUMN {$column['name']} {$column['data_type']}";
        if ($column['length']) {
            $sql .= "({$column['length']})";
        }
        if ($column['default']) {
            $sql .= " DEFAULT {$column['default']}";
        }
        if ($column['nullable']) {
            $sql .= " NULL";
        } else {
            $sql .= " NOT NULL";
        }

        if ($column['auto_increment']) {
            $sql .= " AUTO_INCREMENT";
        }

        if ($column['primary_key']) {
            $sql .= " PRIMARY KEY";
        }

        if ($column['unique']) {
            $sql .= " UNIQUE";
        }

        if ($column['index']) {
            $sql .= " INDEX";
        }

        if ($column['foreign_key']) {
            $sql .= " FOREIGN KEY";
        }

        if ($column['comment']) {
            $sql .= " COMMENT '{$column['comment']}'";
        }

        $sql .= ";\n";
        return $sql;
    }

    private function resloveDropColumn($sql, $column)
    {
        $sql .= "ALTER TABLE {$this->bindings['table']} DROP COLUMN {$column['name']};\n";
        return $sql;
    }

    private function resloveRenameColumn($sql, $column)
    {
        $sql .= "ALTER TABLE {$this->bindings['table']} RENAME COLUMN {$column['name']} TO {$column['new_name']};\n";
        return $sql;
    }

    private function resloveAddIndex($sql, $column)
    {
        $sql .= "ALTER TABLE {$this->bindings['table']} ADD INDEX {$column['index_name']}({$column['name']});\n";
        return $sql;
    }

    private function resloveDropIndex($sql, $column)
    {
        $sql .= "ALTER TABLE {$this->bindings['table']} DROP INDEX {$column['index_name']};\n";
        return $sql;
    }

}