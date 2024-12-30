<?php
namespace Hola\Core;
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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function configure()
    {
        $command = $this->setName($this->command)
            ->setDescription($this->command_description);

        if(!empty($this->arguments)) {
            foreach ($this->arguments as $key=>$argument) {
                if(preg_match('/^[?]/', $argument)) {
                    $argument = str_replace('?','',$argument);
                    $this->arguments[$key] = $argument;
                    $command->addArgument($argument, InputArgument::OPTIONAL);
                } else {
                    $command->addArgument($argument, InputArgument::REQUIRED);
                }
            }
        }

        if(!empty($this->options)) {
            foreach ($this->options as $key=>$options) {
                if(preg_match('/^[?]/', $options)) {
                    $options = str_replace('?','', $options);
                    $this->options[$key] = $options;
                    $command->addOption($options, null, InputOption::VALUE_OPTIONAL, '');
                } else {
                    $command->addOption($options,null, InputOption::VALUE_REQUIRED, '');
                }
            }
        }
    }

    /**
     * @param InputInterface $input
     * @return void
     */
    private function setOptions(InputInterface $input) {
        foreach ($this->options as $option) {
            $this->list_options[$option] = $input->getOption($option);
        }
    }

    /**
     * @param InputInterface $input
     * @return void
     */
    private function setArguments(InputInterface $input) {
        foreach ($this->arguments as $argument) {
            $this->list_arguments[$argument] = $input->getArgument($argument);
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getOption($key) {
        return $this->list_options[$key];
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getArgument($key) {
        return $this->list_arguments[$key];
    }

    /**
     * @param int $count_number
     * @return ProgressBar
     */
    protected function createProgressBar($count_number = 0) {
        if ($this->styleSymfony instanceof SymfonyStyle) {
            $this->progressBar = $this->styleSymfony->createProgressBar($count_number);
        }
        return $this->progressBar;
    }

    /**
     * @return ProgressBar
     */
    protected function output()
    {
        return $this->styleSymfony instanceof SymfonyStyle ? $this->styleSymfony:$this->styleSymfony;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
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