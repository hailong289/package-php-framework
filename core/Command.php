<?php
namespace System\Core;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Command extends SymfonyCommand {

    private $list_options = [];
    private $list_arguments = [];
    private $progressBar;
    private $styleSymfony;

    public function __construct() {
        parent::__construct();
    }

    protected function configure()
    {
        $command = $this->setName($this->command)
            ->setDescription($this->command_description);

        if(!empty($this->arguments)) {
            foreach ($this->arguments as $argument) {
                if(preg_match('/^[?]/', $argument)) {
                    $command->addArgument($argument);
                } else {
                    $command->addArgument($argument, InputArgument::REQUIRED);
                }
            }
        }

        if(!empty($this->options)) {
            foreach ($this->options as $options) {
                if(preg_match('/^[?]/', $options)) {
                    $command->addOption($options);
                } else {
                    $command->addOption($options, InputOption::VALUE_REQUIRED);
                }
            }
        }
    }

    private function setOptions(InputInterface $input) {
        foreach ($this->options as $option) {
            $this->list_options[$option] = $input->getOption($option);
        }
    }

    private function setArguments(InputInterface $input) {
        foreach ($this->arguments as $argument) {
            $this->list_arguments[$argument] = $input->getArgument($argument);
        }
    }

    protected function getOption($key) {
        return $this->list_options[$key];
    }

    protected function getArgument($key) {
        return $this->list_arguments[$key];
    }

    protected function createProgressBar($count_number = 0) {
        if ($this->styleSymfony instanceof SymfonyStyle) {
            $this->progressBar = $this->styleSymfony->createProgressBar($count_number);
        }
        return $this->progressBar;
    }

    protected function output()
    {
        return $this->styleSymfony instanceof SymfonyStyle ? $this->styleSymfony:$this->styleSymfony;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $method = method_exists($this, 'handle') ? 'handle' : '__invoke';
        if(!empty($this->options)) $this->setOptions($input);
        if(!empty($this->arguments)) $this->setArguments($input);
        $this->styleSymfony = new SymfonyStyle($input, $output);
        try {
            $this->$method();
            return SymfonyCommand::SUCCESS;
        }catch (\Exception $e) {
            return SymfonyCommand::FAILURE;
        }
    }
}