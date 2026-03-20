<?php

namespace Hola\Scripts\Commands\Schedule;

abstract class ScheduleManage
{
    protected array $tasks = [];
    protected string $cli;
    protected string $lockDirectory;

    public function __construct()
    {
        $this->cli = PHP_BINARY . ' cli.php ';
        $this->lockDirectory = __DIR__ROOT . '/storage/cache/schedule_locks';
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
        $lockFile = null;
        $releaseLockInFinally = false;

        echo "\033[0;32m[Schedule]\033[0m " . date('Y-m-d H:i:s') . " - Running: {$command}\n";

        if ($task->withoutOverlap) {
            $lockFile = $this->acquireTaskLock($task);
            if ($lockFile === null) {
                echo "\033[0;33m[Skip]\033[0m Task is already running (withoutOverlap): {$task->command}\n";
                return;
            }
            $releaseLockInFinally = !$task->background;
        }
        
        try {
            if ($task->background) {
                if ($lockFile !== null) {
                    $lockArg = escapeshellarg($lockFile);
                    $script = "echo $$ > {$lockArg}; trap 'rm -f {$lockArg}' EXIT; {$command}";
                    $backgroundCommand = 'sh -c ' . escapeshellarg($script) . ' > /dev/null 2>&1 &';
                    exec($backgroundCommand, $output, $exitCode);
                    if ($exitCode !== 0) {
                        $this->releaseTaskLock($lockFile);
                        echo "\033[0;31m[Error]\033[0m Failed to start background task: {$task->command}\n";
                    } else {
                        echo "\033[0;34m[Background]\033[0m Task queued to run in background\n";
                    }
                    return;
                }

                exec($command . ' > /dev/null 2>&1 &');
                echo "\033[0;34m[Background]\033[0m Task queued to run in background\n";
                return;
            }

            if ($task->queue) {
                echo "\033[0;34m[Queue]\033[0m Task queued for later execution\n";
                return;
            }

            $attempts = max(1, $task->retry + 1);
            for ($i = 0; $i < $attempts; $i++) {
                if ($i > 0) {
                    echo "\033[0;32m[Retry]\033[0m Attempt {$i} of {$attempts}\n";
                }

                exec($command . ' 2>&1', $output, $exitCode);

                if ($exitCode === 0) {
                    break;
                }
            }

            if (!empty($output)) {
                echo implode(PHP_EOL, $output) . PHP_EOL;
            }

            if ($exitCode !== 0) {
                $logMessage = sprintf(
                    "[%s] SCHEDULE FAILED: %s (Exit: %d)\n",
                    date('Y-m-d H:i:s'),
                    $command,
                    $exitCode
                );
                file_put_contents(__DIR__ROOT . '/storage/scheduler.log', $logMessage, FILE_APPEND);
            }
        } finally {
            if ($releaseLockInFinally && $lockFile !== null) {
                $this->releaseTaskLock($lockFile);
            }
        }
    }

    protected function acquireTaskLock(ScheduledTask $task): ?string
    {
        if (!is_dir($this->lockDirectory)) {
            mkdir($this->lockDirectory, 0777, true);
        }

        $lockName = md5($task->command) . '.lock';
        $lockFile = $this->lockDirectory . '/' . $lockName;

        if (file_exists($lockFile)) {
            $pid = (int) trim((string) file_get_contents($lockFile));
            if ($pid > 0 && $this->isProcessRunning($pid)) {
                return null;
            }

            @unlink($lockFile);
        }

        file_put_contents($lockFile, (string) getmypid());

        return $lockFile;
    }

    protected function releaseTaskLock(string $lockFile): void
    {
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }
    }

    protected function isProcessRunning(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }

        if (function_exists('posix_kill')) {
            return @posix_kill($pid, 0);
        }

        exec('ps -p ' . (int) $pid . ' > /dev/null 2>&1', $output, $exitCode);
        return $exitCode === 0;
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