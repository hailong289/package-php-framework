<?php
namespace Scripts;
class JobsScript extends \System\Core\Command
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