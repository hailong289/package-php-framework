<?php
namespace Queue;
class Job1 {
   public $params1 = 0;
   public $params2 = 0;
   public function __construct($params1, $params2)
   {
       $this->params1 = $params1;
       $this->params2 = $params2;
   }
   public function handle(){
       echo 'job1';
   }
}