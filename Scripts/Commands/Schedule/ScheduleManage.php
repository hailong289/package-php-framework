<?php

namespace Hola\Scripts\Commands\Schedule;

use Throwable;

abstract class ScheduleManage
{
    protected array $tasks = [];
    protected string $cli;
    protected string $lockDirectory;
    protected string $schedulerLog;

    public function __construct()
    {
        $php_binary = PHP_BINARY ?: PHP_BINDIR . '/php';
        if (!is_executable($php_binary)) {
            $php_binary = 'php';
        }
        $this->cli = $php_binary . ' cli.php ';
        $this->lockDirectory = __DIR__ROOT . '/storage/cache/schedule_locks';
        $this->schedulerLog = __DIR__ROOT . '/storage/scheduler.log';
    }

    abstract public function handle();

    public function command(string $command): ScheduledTask
    {
        $task = new ScheduledTask($command);

        $this->tasks[] = $task;

        return $task;
    }


    public function run($is_dev = false): void
    {
        $this->handle();
        if ($is_dev) {
            while (true) {
                foreach ($this->tasks as $task) {
                    if (!$this->isDue($task->expression)) {
                        continue;
                    }

                    try {
                        $this->executeTask($task);
                    } catch (Throwable $e) {
                        $this->logSchedulerMessage('error', 'Task crashed: ' . $task->command, $e);
                        echo "\033[0;31m[Error]\033[0m Task crashed and was skipped: {$task->command}\n";
                    }
                }
                sleep(1);
            }
        } else {
            foreach ($this->tasks as $task) {
                if (!$this->isDue($task->expression)) {
                    continue;
                }
                try {
                    $this->executeTask($task);
                } catch (Throwable $e) {
                    $this->logSchedulerMessage('error', 'Task crashed: ' . $task->command, $e);
                    echo "\033[0;31m[Error]\033[0m Task crashed and was skipped: {$task->command}\n";
                }
            }
        }
    }

    protected function executeTask(ScheduledTask $task): void
    {
        $command = $this->cli . $task->command;
        $output = '';
        $exitCode = 0;
        $lockFile = null;
        $releaseLockInFinally = false;

        $this->output('info', 'RUNNING',date('Y-m-d H:i:s') . " - Command: {$command}");
        if ($task->withoutOverlap) {
            $lockFile = $this->acquireTaskLock($task);
            if ($lockFile === null) {
                $this->output('info', 'RUNNING', "Task is already running (withoutOverlap): {$task->command}");
                return;
            }
            $releaseLockInFinally = !$task->background;
        }

        try {
            if ($task->background) {
                if ($lockFile !== null) {
                    $lockArg = escapeshellarg($lockFile);
                    $script = "echo $$ > {$lockArg}; trap 'rm -f {$lockArg}' EXIT; {$command}";
                    $result = bash()->run('sh', '-c', $script . ' > /dev/null 2>&1 &');
                    $exitCode = $result->exitCode();
                    if ($exitCode !== 0) {
                        $this->releaseTaskLock($lockFile);
                        $this->logSchedulerMessage('error', 'Failed to start background task: ' . $task->command . ' (Exit: ' . $exitCode . ')');
                        $this->output('error', 'ERROR', "Failed to start background task: {$task->command}\n");
                    } else {
                        $this->output('info', 'INFO', "Task queued to run in background");
                    }
                    return;
                }

                $result = bash()->run('sh', '-c', $command . ' > /dev/null 2>&1 &');
                if ($result->ok()) {
                    $this->output('info', "INFO", "Task queued to run in background");
                } else {
                    $this->logSchedulerMessage('error', 'Failed to start background task: ' . $task->command);
                    $this->output('error','ERROR', "Failed to start background task: {$task->command}\n");
                }
                return;
            }

            if ($task->queue) {
                $this->output('warning', 'WARNING', "Task queued for later execution");
                return;
            }

            $attempts = max(1, $task->retry + 1);
            for ($i = 0; $i < $attempts; $i++) {
                if ($i > 0) {
                    $this->output('info', 'RETRY', "Attempt {$i} of {$attempts}", date('Y-m-d H:i:s'));
                }

                $result = bash()->run('sh', '-c', $command . ' 2>&1');
                $output = $result->output();
                $exitCode = $result->exitCode();

                if ($exitCode === 0) {
                    break;
                }
            }

            if (trim($output) !== '') {
                echo rtrim($output, "\r\n") . PHP_EOL;
            }

            if ($exitCode !== 0) {
                $this->logSchedulerMessage('error', sprintf('SCHEDULE FAILED: %s (Exit: %d)', $command, $exitCode));
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

        return bash()->run('ps', '-p', (string) $pid)->ok();
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

    protected function logSchedulerMessage(string $level, string $message, ?Throwable $exception = null): void
    {
        $logLine = sprintf('[%s] %s: %s', date('Y-m-d H:i:s'), strtoupper($level), $message);
        if ($exception !== null) {
            $logLine .= sprintf(' | %s in %s:%d', $exception->getMessage(), $exception->getFile(), $exception->getLine());
        }
        $logLine .= PHP_EOL;
        file_put_contents($this->schedulerLog, $logLine, FILE_APPEND);
    }

    protected function output($color, $title = '', $message = ''): void {
        switch ($color) {
            case 'info':
                echo "\033[0;36m[$title]\033[0m: " . $message . "\n";
                break;
            case 'error':
                echo "\033[0;31m[$title]\033[0m: " . $message . "\n";
                break;
            case 'success':
                echo "\033[0;32m[$title]\033[0m: " . $message . "\n";
                break;
            case 'warning':
                echo "\033[0;33m[$title]\033[0m: " . $message . "\n";
                break;
            default:
                echo "[$title]: " . $message . "\n";
        }
    }
}