<?php

namespace Hola\Scripts\Commands;

use Hola\Core\Command;

class CreateRequest extends Command {
    protected $command = 'create:request';
    protected $command_description = 'Create a new form request';
    protected $arguments = ['arg_request'];
    protected $options = [];

    public function handle()
    {
        $className = $this->getClassName();
        $concurrentDirectory = __DIR__ROOT . "/App/Http/Request/$className.php";
        if (file_exists($concurrentDirectory)) {
            $this->output()->text("Request $className already exist");
            return false;
        }
        $this->createFolderIfNotExits();
        file_put_contents($concurrentDirectory, $this->rawClass($className), FILE_APPEND);
        if (!file_exists($concurrentDirectory)) {
            $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        $this->output()->text("Request $className create successfully");
    }

    public function getClassName()
    {
        $name = $this->getArgument('arg_request');
        if (strpos($name, 'Request') === false) {
            return $name . 'Request';
        }
        return $name;
    }

    public function createFolderIfNotExits()
    {
        if (!is_dir(__DIR__ROOT . "/App/Http/Request")) {
            mkdir(__DIR__ROOT . "/App/Http/Request");
        }
    }

    public function rawClass($className)
    {
        return '<?php
namespace App\Http\Request;
use Hola\Core\FormRequest;

class ' . $className . ' extends FormRequest
{
    public function __construct() {
        parent::__construct();
    }

    public function auth() {
        return true;
    }

    public function rules()
    {
        return [];
    }
}';
    }
}