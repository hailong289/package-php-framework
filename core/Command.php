<?php

namespace System\Core;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand{
    public function __construct() {}
    protected function configure()
    {
        $this->setName($this->command_name)
            ->setDescription($this->command_description);
    }

    protected function execute(InputInterface $input, OutputInterface $output){

    }
}