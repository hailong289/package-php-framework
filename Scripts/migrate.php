<?php

namespace Hola\Scripts;

class MigrateScript extends \Hola\Core\Command {
    protected $command = 'migrate:run';
    protected $command_description = 'Run the database migrations';
    protected $arguments = ['type'];
    protected $options = ['?path'];

    public function __construct() {
        parent::__construct();
    }

    public function handle() {
        $migrations = $this->getMigrations();
        $type = $this->getArgument('type');
        if ($type != 'up' && $type != 'down') {
            $this->output()->text('Type must be up or down');
            return;
        }
        if ($type == 'down') {
            $migrations = array_reverse($migrations);
        }
        foreach ($migrations as $migration) {
            $this->runMigration($migration, $type);
        }
        $this->output()->text('Migrations run successfully');
    }

    protected function getMigrations() {
        $migrationPath = $this->getOption('path');
        if (!empty($migrationPath)) {
            $migration = str_replace('.php', '', $migrationPath);
            $migration = str_replace(__DIR__ROOT . 'database/migrations/', '', $migration);
            return [$migration];
        }
        $migrations = rglob(__DIR__ROOT . 'database/migrations/*.php') ?? [];
        $migrationsClass = array_map(function ($migration) {
            $migration = str_replace('.php', '', $migration);
            $migration = str_replace(__DIR__ROOT . 'database/migrations/', '', $migration);
            return $migration;
        }, $migrations);
        return $migrationsClass;
    }

    protected function runMigration($migration, $type = 'up') {
        $typeRun = 'run' . ucfirst($type);
        $migration = 'App\\Database\\Migrations\\' . $migration;
        $migration = new $migration;
        $migration->{$typeRun}();
        $this->output()->text('Migration ' . $migration . ' run successfully');
    }
}