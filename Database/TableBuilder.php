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
        'selected_column' => null,
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
        $type
    ) {
        $this->bindings['selected_column'] = $column;
        $this->bindings['columns'][$column] = [
            'table' => $this->bindings['table'],
            'name' => $column,
            'type' => $type,
            'category' => 'add',
        ];
        return $this->resloveBound($column);
    }

    public function dropColumn($column) {
        $this->bindings['selected_column'] = $column;
        $this->bindings['columns'][$column] = [
            'table' => $this->bindings['table'],
            'name' => $column,
            'type' => $type,
            'category' => 'drop'
        ];
        return $this;
    }

    public function modifyColumn(
        $column,
        $type
    ) {
        $this->bindings['selected_column'] = $column;
        $this->bindings['columns'][$column] = [
            'table' => $this->bindings['table'],
            'name' => $column,
            'type' => $type,
            'category' => 'modify'
        ];
        return $this->resloveBound($column);
    }

    public function changeColumn(
        $column,
        $type
    ) {
        $this->bindings['selected_column'] = $column;
        $this->bindings['columns'][$column] = [
            'table' => $this->bindings['table'],
            'name' => $column,
            'category' => 'change',
        ];
        return $this->resloveBound($column);
    }

    public function renameColumn($column, $newName) {
        $this->bindings['columns'][$column] = [
            'table' => $this->bindings['table'],
            'name' => $column,
            'category' => 'rename',
        ];
        return $this;
    }

    public function addIndex($column, $indexName = null, $type_index = 'INDEX') {
        $this->bindings['columns'][$column] = [
            'table' => $this->bindings['table'],
            'type' => 'add_index',
            'name' => $column,
            'index' => [
                'name' => $indexName,
                'type' => $type_index
            ]
        ];
        return $this;
    }

    public function dropIndex($column, $indexName = null, $type_index = 'INDEX') {
        $this->bindings['columns'][$column] = [
            'table' => $this->bindings['table'],
            'type' => 'drop_index',
            'index' => [
                'name' => $name,
                'type' => $type_index
            ]
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
            switch ($column['category']) {
                case 'add':
                    $sql .= $this->resloveAddColumn($sql, $column);
                    break;
                case 'modify':
                    $sql .= $this->resloveModifyColumn($sql, $column);
                    break;
                case 'change':
                    $sql .= $this->resloveChangeColumn($sql, $column);
                    break;
                case 'drop':
                    $sql .= $this->resloveDropColumn($sql, $column);
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
        $sql .= "ALTER TABLE {$this->bindings['table']} ADD COLUMN {$column['name']}";
        $sql = $this->resloveColumnType($sql, $column);
        $sql .= ";\n";
        return $sql;
    }

    private function resloveModifyColumn($sql, $column)
    {
        $sql .= "ALTER TABLE {$this->bindings['table']} MODIFY COLUMN {$column['name']} {$column['type']}";
        $sql = $this->resloveColumnType($sql, $column);
        $sql .= ";\n";
        return $sql;
    }

    private function resloveChangeColumn($sql, $column)
    {
        $sql .= "ALTER TABLE {$this->bindings['table']} CHANGE COLUMN {$column['name']} {$column['type']};\n";
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

    private function resloveAddIndex($sql, $column, $type_index = 'INDEX')
    {

        $sql .= "ALTER TABLE {$this->bindings['table']} ADD $type_index {$column['index']['name']}({$column['name']});\n";
        return $sql;
    }

    private function resloveDropIndex($sql, $column, $type_index = 'INDEX')
    {
        $sql .= "ALTER TABLE {$this->bindings['table']} DROP $type_index {$column['index']['name']};\n";
        return $sql;
    }

    private function resloveColumnType($sql = '', $column = [])
    {
        if (empty($column['type'])) {
            throw new \Exception('Type column is required');
        }

        $sql .= " {$column['type']}";

        if (!empty($column['length'])) {
            $sql .= "({$column['length']})";
        }

        if (!empty($column['values'])) {
            $sql .= "({$column['values']})";
        }

        if (!empty($column['default'])) {
            $sql .= " DEFAULT {$column['default']}";
        }

        if (!empty($column['nullable'])) {
            $sql .= " NULL";
        } else {
            $sql .= " NOT NULL";
        }

        if (!empty($column['auto_increment'])) {
            $sql .= " AUTO_INCREMENT";
        }

        if (!empty($column['unsigned'])) {
            $sql .= " UNSIGNED";
        }

        if (!empty($column['primary_key'])) {
            $sql .= " PRIMARY KEY";
        }

        if (!empty($column['foreign_key'])) {
            $sql .= " FOREIGN KEY";
        }

        if (!empty($column['unique'])) {
            $sql .= " UNIQUE";
        }

        if (!empty($column['comment'])) {
            $sql .= " COMMENT '{$column['comment']}'";
        }

        if (!empty($column['index'])) {
            $sql .= ";{$this->resloveAddIndex($sql, $column, $column['index']['type'])}";
        }

        return $sql;
    }

    public function resloveBound($column, $options = [
        'length' => null,
        'values' => null,
        'default' => null,
        'nullable' => false,
        'auto_increment' => false,
        'unsigned' => false,
        'primary_key' => false,
        'foreign_key' => false,
        'unique' => false,
        'comment' => null,
        'index' => null
    ])
    {
        $binding_column = $this->bindings['columns'][$column];
        $this->bindings['columns'][$column] = array_merge($binding_column, $options);
        return $this;
    }
}