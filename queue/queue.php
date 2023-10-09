<?php
namespace Queue;
class CreateQueue
{
    public $front;
    public $rear;

    public $queue = array();

    function __construct() {
        $this->rear = -1;
        $this->front = -1;
    }

    // create a function to check whether
    // the queue is empty or not
    public function isEmpty() {
        if($this->rear == $this->front) {
//            echo "Queue is empty. \n";
            return true;
        } else {
//            echo "Queue is not empty. \n";
            return false;
        }
    }

    //create a function to return size of the queue
    public function size() {
        return ($this->rear - $this->front);
    }

    //create a function to add new element
    public function enQueue($x) {
        $rear = ++$this->rear;
        $this->queue[$rear] = $x;
        if (isset($this->queue[$rear]) && method_exists($this->queue[$rear],'handle')) {
            try {
                $this->queue[$rear]->handle();
                $this->deQueue();
            }catch (\Throwable $e) {
                $this->deQueue();
                log_write($e);
            }
        } else {
            $this->deQueue();
            die('class handle does not exit');
        }
    }

    //create a function to delete front element
    public function deQueue() {
        if($this->rear == $this->front){
//            echo "Queue is empty. \n";
        } else {
            $x = $this->queue[++$this->front];
        }
    }

    //create a function to get front element
    public function frontElement() {
        if($this->rear == $this->front) {
//            echo "Queue is empty. \n";
        } else {
            return $this->queue[$this->front+1];
        }
    }

    //create a function to get rear element
    public function rearElement() {
        if($this->rear == $this->front) {
//            echo "Queue is empty. \n";
        } else {
            return $this->queue[$this->rear];
        }
    }
}