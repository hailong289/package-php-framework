<?php

namespace Hola\Scripts\Commands;
use App\Commands\Kernel;
use Hola\Core\Command;

class ScheduleRun extends Command
{
    protected $command = 'schedule:run';
    protected $command_description = 'Run the scheduled commands';
    protected $arguments = ['?environment'];
    protected $options = [];
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        try {
            $isDev = $this->getArgument('environment') === 'dev';
            app(Kernel::class)->run($isDev);
        } catch (\Exception $e) {
            $this->output()->writeln("<error>Error running scheduled commands: {$e->getMessage()}</error>");
        }
    }
}