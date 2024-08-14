<?php
namespace Hola\Scripts;
class JobsScript extends \Hola\Core\Command
{
    protected $command = 'create:jobs';
    protected $command_description = 'Create a new job';
    protected $arguments = [
        'name_job'
    ];
    protected $options = [];

    public function handle()
    {
        $name_job = $this->getArgument('name_job');
        $concurrentDirectory = __DIR__ROOT . "/queue/$name_job.php";
        if (!file_exists($concurrentDirectory)) {
            if (!is_dir(__DIR__ROOT . "/queue")) {
                mkdir(__DIR__ROOT . "/queue");
            }
            file_put_contents($concurrentDirectory, '<?php
namespace Queue\Jobs;
class '.$name_job. ' {
   public function __construct(){}
   public function handle(){
       // code here
   }
}', FILE_APPEND);
            if (!file_exists($concurrentDirectory)) {
                $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
                return;
            }
            $this->output()->text("Jobs $name_job create successfully");
        } else {
            $this->output()->text("Jobs $name_job already exist");
        }
    }
}