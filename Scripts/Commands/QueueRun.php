<?php
namespace Hola\Scripts\Commands;

use Hola\Connection\RabbitMQ;
use Hola\Connection\Redis;
use Hola\Core\TimeoutManager;
use Hola\Database\DBO;
use Hola\Database\QueryBuilder;
use Hola\Exceptions\QueueException;
use Hola\Queue\ListenQueue;
use Hola\Scripts\Commands\QueueJobs\QueueManage;
use Hola\Scripts\Commands\QueueJobs\SwitchDB;

class QueueRun extends \Hola\Core\Command
{
    protected $command = 'queue:run';
    protected $command_description = 'Run a queue';
    protected $arguments = ['?connection_type'];
    protected $options = ['?queue','?timeout','?connection', '?failed_rollback', '?delay'];
    protected $jobs_queue = 'jobs';
    protected $connection = 'database';
    protected $connection_type = 'database';
    protected $timeout = 600; // default 10 minutes
    protected $failed_rollback = false; // true, false
    protected $delay = 5;

    public function __construct()
    {
        $this->timeout = config('queue.timeout');
        parent::__construct();
    }

    public function handle()
    {
        $this->connection_type = conval('QUEUE_WORK', 'database');
        $this->connection = conval('QUEUE_CONNECTION', 'database');
        $connection_type = $this->getArgument('connection_type');
        $connection = $this->getOption('connection');
        $timeout_options = $this->getOption('timeout');
        $queue_name = $this->getOption('queue');
        $failed_rollback = $this->getOption('failed_rollback') ?? false;
        $delay_options = $this->getOption('delay') ?? 5;
        if (!empty($connection_type)) {
            $this->connection_type = $connection_type;
        }
        if (!empty($queue_name)) {
            $this->jobs_queue = $queue_name;
        }
        if (!empty($connection)) {
            $this->connection = $connection;
        }
        if (!empty($timeout_options)) {
            $this->timeout = $timeout_options;
        }
        $this->failed_rollback = $failed_rollback;
        $this->delay = $delay_options;
        $this->handleQueue();
    }

    public function handleQueue()
    {
        try {
            $queueManage = new QueueManage();
            $queueManage->setOutput($this->output());
            $queueManage->setTimeout($this->timeout);

            $switchDB = new SwitchDB($this->connection_type);
            $switchDB->handle()
                ->setQueueName($this->jobs_queue)
                ->setQueueConnection($this->connection);

            if ($this->failed_rollback) {
                $switchDB->setRollBackJob($this->failed_rollback);
            }

            $switchDB->getDriver()->queueWork($queueManage);
            $this->output()->info("Queue worked. Watting for new job...");
            sleep($this->delay);
            exit();
        } catch (\Throwable $th) {
            logs()->write_error($th);
            $this->output()->error($th->getMessage());
            return false;
        }
    }


}
