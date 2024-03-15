<?php
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
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
    echo "Jobs $name_job create successfully";
} else {
    echo "Jobs $name_job already exist";
}
