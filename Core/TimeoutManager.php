<?php

namespace Hola\Core;

class TimeoutManager
{
    private $timeout;
    private $triggers = [];
    private $payload;

    public function __construct(int $timeout = 5)
    {
        $this->timeout = $timeout;
    }

    private function registerSignalHandler()
    {
        pcntl_signal(SIGALRM, function () {
            if (isset($this->triggers['callback'])) {
                $this->triggers['callback']($this->payload);
            } else {
                echo "Task {$this->taskName} timeout\n";
            }
            exit;
        });
    }

    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    public function execute(string $taskName, callable $task, $data = [])
    {
        if (!extension_loaded('pcntl')) {
            throw new \Exception('PCNTL extension is NOT enabled.');
        }
        $this->payload = [
            'taskName' => $taskName,
            'data' => $data
        ];
        $this->registerSignalHandler();
        pcntl_alarm($this->timeout);
        $task();
        pcntl_signal_dispatch();
    }

    public function eventTimeOut($callback) {
         $this->triggers['callback'] = $callback;
         return $this;
    }
}