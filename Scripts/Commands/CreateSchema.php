<?php

namespace Hola\Scripts\Commands;

use Hola\Core\Command;

class CreateSchema extends Command {
    protected $command = 'create:schema';
    protected $command_description = 'Run the database create schema';
    protected $options = [
        'class',
        'table',
        'type',
        '?name_file'
    ];

    public function __construct() {
        parent::__construct();
    }

    public function handle()
    {
        $className = $this->getOption('class');
        $table_name = $this->getOption('table');
        $type = $this->getOption('type');
        if (!$className || !$table_name || !$type) {
            if (!$className) $this->output()->text('The --class option is required.');
            if (!$table_name) $this->output()->text('The --table option is required.');
            if (!$type) $this->output()->text('The --type option is required.');
            return;
        }

        if ($type != 'create' && $type != 'use' && $type != 'add_column') {
            $this->output()->text('Type must be `create` or `use` or `add_column`');
            return;
        }
        $this->runMigration($className, $table_name, $type);
    }

    public function runMigration($className, $table_name, $type)
    {
        $method = $type === 'use' || $type === 'add_column' ? 'useTable' : 'createTable';
        $table_name = strtolower($table_name);
        $firstChar = ucfirst($type);
        $nameFile = $this->getOption('name_file');
        $migration = date('Y_m_d_His'). ($nameFile ? $nameFile : "_$type_".$table_name).'_table';
        $migrationPath = __DIR__ROOT . "/database/SchemaMigrate/".str_slug($migration,'_').".php";
        if (!file_exists($migrationPath)) {
            createFolder(__DIR__ROOT . "/database/SchemaMigrate");
            file_put_contents($migrationPath, '<?php
namespace App\Database\SchemaMigrate;
use Hola\Database\Structure\Table;
use Hola\Database\Structure\DBSchema;
use Hola\Database\TableMigration;

class ' . $className . ' extends TableMigration {
    public function up() {
         DBSchema::'.$method.'(\'' . $table_name . '\', function(Table $table) {
            
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