<?php

namespace Hola\Scripts;

class SchemaRunScript extends \Hola\Core\Command {
    protected $command = 'schema:run';
    protected $command_description = 'Run the database schema';
    protected $arguments = ['type'];
    protected $options = ['?path'];

    public function __construct() {
        parent::__construct();
    }

    public function handle() {
        $type = $this->getArgument('type');
        if ($type != 'up' && $type != 'down') {
            $this->output()->text('Type must be up or down');
            return;
        }

        $migrations = $this->getMigrations();

        if ($type == 'down') {
            $migrations = array_reverse($migrations);
        }

        foreach ($migrations as $migration) {
            $this->runMigration($migration, $type);
        }
    }

    protected function getMigrations() {
        $migrationPath = $this->getOption('path');
        if (!empty($migrationPath)) {
            $migration = str_replace('.php', '', $migrationPath);
            $migration = str_replace(__DIR__ROOT . '/database/SchemaMigrate/', '', $migration);
            return [$migration];
        }
        $migrations = rglob(__DIR__ROOT . '/database/SchemaMigrate/*.php') ?? [];
        $migrationsClass = array_map(function ($migration) {
            $migration = $this->getClassesFromFile($migration);
            return $migration;
        }, $migrations);
        return $migrationsClass;
    }

    protected function runMigration($migration, $type = 'up') {
        try {
            $typeRun = 'run' . ucfirst($type);
            app('\\App\\Database\\SchemaMigrate\\' . $migration)->{$typeRun}();
            $this->output()->text('Migration ' . $migration . ' run successfully');
        } catch (\Throwable $e) {
            $this->output()->error([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return;
        }
    }

    protected function getClassesFromFile($filePath) {
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        $tokens = token_get_all($content);
        $classes = [];
        $namespace = '';
        $isNamespace = false;

        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];

            if (is_array($token)) {
                if ($token[0] === T_NAMESPACE) {
                    $namespace = '';
                    $isNamespace = true;
                }
                if ($isNamespace && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR)) {
                    $namespace .= $token[1];
                }
                if ($isNamespace && $token === ';') {
                    $isNamespace = false;
                }
                if ($token[0] === T_CLASS && isset($tokens[$i + 2][1])) {
                    $className = $tokens[$i + 2][1];
                    $classes[] = $namespace ? "$namespace\\$className" : $className;
                }
            }
        }
        return $classes[0];
    }
}