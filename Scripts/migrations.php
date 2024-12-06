<?php

namespace Hola\Scripts;

class MigrationsScript extends \Hola\Core\Command {
    protected $command = 'create:migration';
    protected $command_description = 'Run the database create migration';
    protected $arguments = ['table', 'type', '?name_file'];

    public function handle()
    {
        $table_name = $this->getArgument('table');
        $type = $this->getArgument('type');
        if ($type != 'create' && $type != 'use' && $type != 'add_column') {
            $this->output()->text('Type must be `create` or `use` or `add_column`');
            return;
        }
        $this->runMigration($table_name, $type);
    }

    public function runMigration($table_name, $type)
    {
        $method = $type === 'use' || $type === 'add_column' ? 'useTable' : 'createTable';
        $table_name = strtolower($table_name);
        $firstChar = ucfirst($type);
        $class = $firstChar . str_replace('_', '', $table_name);
        $nameFile = $this->getArgument('name_file');
        $migration = date('Y_m_d_His'). ($nameFile ? $nameFile : "_$type_".$table_name).'_table';
        $migrationPath = __DIR__ROOT . "/database/migrations/$migration.php";
        if (!file_exists($migrationPath)) {
            createFolder(__DIR__ROOT . "/database/migrations");
            file_put_contents($migrationPath, '<?php
namespace App\Database\Migrations;
use Hola\Database\Structure\Table;
use Hola\Database\Structure\SchemaManager;
class ' . $class . ' {
    public function up() {
        SchemaManager::'.$method.'(\'' . $table_name . '\', function(Table $table) {
            
        });
    }
    public function down() {
    }
    
}', FILE_APPEND);
            $this->output()->text("$migration create successfully".PHP_EOL);
        } else {
            $this->output()->text("$migration already exist".PHP_EOL);
        }
    }
}