<?php

namespace Hola\Scripts;

class GenerateScript extends \Hola\Core\Command
{
    protected $command = 'generate:key';
    protected $command_description = 'Generate a project key';
    protected $arguments = [];
    protected $options = [];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $new_project_key = generateKey(32);
        $concurrentDirectory = __DIR__ROOT . "/config/constant.php";
        if (!file_exists($concurrentDirectory)) {
            $this->output()->text(sprintf('File "%s" was not found', $concurrentDirectory));
            return;
        }

        $file_contents = file_get_contents($concurrentDirectory);
        if (preg_match('/const PROJECT_KEY.*=.*\'.*?\'/', $file_contents)) {
            $file_contents = preg_replace(
                '/const PROJECT_KEY.*=.*\'.*?\'/',
                'const PROJECT_KEY=\'' . $new_project_key . '\'',
                $file_contents
            );
        } else {
            $file_contents .= PHP_EOL . 'const PROJECT_KEY=\'' . $new_project_key . '\';';
        }
        file_put_contents($concurrentDirectory, $file_contents);
        $this->output()->text("Key generate successfully. KEY:$new_project_key" . PHP_EOL);
    }
}