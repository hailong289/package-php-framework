<?php

namespace Scripts;

use System\Core\Command;

class MailScript extends Command
{
    protected $command = 'create:mail';
    protected $command_description = 'Create a new mail';
    protected $arguments = ['name_mail'];
    protected $options = [];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $name_mail = $this->getArgument('name_mail');
        if (strpos($name_mail, 'Mail') === false) $name_mail = $name_mail . 'Mail';
        $concurrentDirectory = __DIR__ROOT . "/mails/$name_mail.php";
        if (!file_exists($concurrentDirectory)) {
            if (!is_dir(__DIR__ROOT . "/mails")) {
                mkdir(__DIR__ROOT . "/mails");
            }
            file_put_contents($concurrentDirectory, '<?php
namespace Mails;
use System\Core\Mail;
class '.$name_mail.' extends Mail {
    protected $useQueue = false;
    public function __construct()
    {
        parent::__construct();
    }
   
    public function handle()
    {
         
    }
}', FILE_APPEND);
            if (!file_exists($concurrentDirectory)) {
                $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
                return;
            }
            $this->output()->text("$name_mail create successfully".PHP_EOL);
        } else {
            $this->output()->text("$name_mail already exist".PHP_EOL);
        }
    }
}