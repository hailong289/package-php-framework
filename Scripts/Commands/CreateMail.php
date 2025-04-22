<?php

namespace Hola\Scripts\Commands;
use Hola\Core\Command;
use Hola\Mailing\MailerBuilder;

class CreateMail extends Command {
    protected $command = 'create:mail';
    protected $command_description = 'Create a new mail';
    protected $arguments = ['arg_mail'];
    protected $options = [];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $className = $this->getClassName();
        $concurrentDirectory = __DIR__ROOT . "/App/Mails/$className.php";
        if (file_exists($concurrentDirectory)) {
            $this->output()->text("$className already exist" . PHP_EOL);
            return false;
        }
        $this->createFolderIfNotExits();
        file_put_contents($concurrentDirectory, $this->rawClass($className), FILE_APPEND);
        if (!file_exists($concurrentDirectory)) {
            $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
            return;
        }
        $this->output()->text("$className create successfully" . PHP_EOL);
    }

    public function getClassName()
    {
        $name = $this->getArgument('arg_mail');
        if (strpos($name, 'Mail') === false) {
            return $name . 'Mail';
        }
        return $name;
    }

    public function createFolderIfNotExits()
    {
        if (!is_dir(__DIR__ROOT . "/App/Mails")) {
            mkdir(__DIR__ROOT . "/App/Mails");
        }
    }

    public function rawClass($className)
    {
        return '<?php
namespace App\Mails;
use Hola\Mailing\MailerBuilder;
class ' . $className . ' extends MailerBuilder {
    public function __construct()
    {
        parent::__construct();
    }
   
    public function handle()
    {
         
    }
}';
    }
}