<?php

namespace Hola\Scripts\Commands\Schedule;

abstract class ScheduleManage
{
    protected array $tasks = [];
    protected string $cli;

    public function __construct()
    {
        $this->cli = PHP_BINARY . ' cli.php ';
    }

    abstract public function handle();

    public function command(string $command): ScheduledTask
    {
        $task = new ScheduledTask($command);

        $this->tasks[] = $task;

        return $task;
    }


    public function run(): void
    {
        $this->handle();
        foreach ($this->tasks as $task) {
            if (!$this->isDue($task->expression)) {
                continue;
            }

            $this->executeTask($task);
        }
    }

    protected function executeTask(ScheduledTask $task): void
    {
        $command = $this->cli . $task->command;
        $output = [];
        $exitCode = 0;

        echo "[schedule] " . date('Y-m-d H:i:s') . " - Running: {$command}\n";
        
        if ($task->background) {
            exec($command . ' > /dev/null 2>&1 &');
            echo "[background] Task queued to run in background\n";
            return;
        }
        
        if ($task->queue) {
            echo "[queue] Task queued for later execution\n";
            return;
        }
        
        $attempts = max(1, $task->retry + 1);
        for ($i = 0; $i < $attempts; $i++) {
            if ($i > 0) {
                echo "[retry] Attempt {$i} of {$attempts}\n";
            }

            exec($command . ' 2>&1', $output, $exitCode);

            if ($exitCode === 0) {
                break;
            }
        }
        
        if (!empty($output)) {
            echo implode(PHP_EOL, $output) . PHP_EOL;
        }
        
        $statusText = $exitCode === 0 ? 'SUCCESS' : 'FAILED';
        echo "[exit-code] {$exitCode} ({$statusText})\n";
        
        if ($exitCode !== 0) {
            $logMessage = sprintf(
                "[%s] SCHEDULE FAILED: %s (Exit: %d)\n",
                date('Y-m-d H:i:s'),
                $command,
                $exitCode
            );
            file_put_contents(__DIR__ROOT . '/storage/scheduler.log', $logMessage, FILE_APPEND);
        }
    }

    protected function isDue(string $expression): bool
    {
        if (empty($expression)) {
            return false;
        }

        $cron = explode(' ', $expression);
        if (count($cron) !== 5) {
            return false;
        }

        $now = [
            (int) date('i'),
            (int) date('H'),
            (int) date('d'),
            (int) date('m'),
            (int) date('w')
        ];

        $ranges = [
            [0, 59],
            [0, 23],
            [1, 31],
            [1, 12],
            [0, 6]
        ];

        foreach ($cron as $i => $value) {
            if (!$this->matchCronField($value, $now[$i], $ranges[$i][0], $ranges[$i][1])) {
                return false;
            }
        }

        return true;
    }

    private function matchCronField(string $field, int $current, int $min, int $max): bool
    {
        foreach (explode(',', $field) as $segment) {
            $segment = trim($segment);
            if ($segment === '*') {
                return true;
            }

            $step = 1;
            if (str_contains($segment, '/')) {
                [$segment, $stepPart] = explode('/', $segment, 2);
                $step = (int) $stepPart;
                if ($step <= 0) {
                    continue;
                }
            }

            $start = $min;
            $end = $max;

            if ($segment !== '*' && str_contains($segment, '-')) {
                [$startPart, $endPart] = explode('-', $segment, 2);
                $start = (int) $startPart;
                $end = (int) $endPart;
            } elseif ($segment !== '*') {
                $start = (int) $segment;
                $end = (int) $segment;
            }

            if ($current < $start || $current > $end) {
                continue;
            }

            if ((($current - $start) % $step) === 0) {
                return true;
            }
        }

        return false;
    }
}