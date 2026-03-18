<?php

namespace Hola\Scripts\Commands\Schedule;

class ScheduledTask
{
    public string $command;
    public string $expression;

    public bool $withoutOverlap = false;
    public bool $background = false;
    public int $retry = 0;
    public bool $queue = false;

    public function __construct(string $command, string $expression = '')
    {
        $this->command = $command;
        $this->expression = $expression;
    }
    
    public function everyMinute(): self
    {
        $this->expression = '* * * * *';
        return $this;
    }

    public function everyFiveMinutes(): self
    {
        $this->expression = '*/5 * * * *';
        return $this;
    }

    public function everyTenMinutes(): self
    {
        $this->expression = '*/10 * * * *';
        return $this;
    }

    public function everyFifteenMinutes(): self
    {
        $this->expression = '*/15 * * * *';
        return $this;
    }

    public function everyThirtyMinutes(): self
    {
        $this->expression = '*/30 * * * *';
        return $this;
    }
    
    public function hourly(): self
    {
        $this->expression = '0 * * * *';
        return $this;
    }

    public function everyTwoHours(): self
    {
        $this->expression = '0 */2 * * *';
        return $this;
    }

    public function everyThreeHours(): self
    {
        $this->expression = '0 */3 * * *';
        return $this;
    }

    public function everyFourHours(): self
    {
        $this->expression = '0 */4 * * *';
        return $this;
    }

    public function everySixHours(): self
    {
        $this->expression = '0 */6 * * *';
        return $this;
    }
    
    public function daily(): self
    {
        $this->expression = '0 0 * * *';
        return $this;
    }

    public function dailyAt(string $time): self
    {
        $parts = explode(':', $time);
        $hour = $parts[0] ?? '0';
        $minute = $parts[1] ?? '0';
        $this->expression = "{$minute} {$hour} * * *";
        return $this;
    }

    public function twiceDaily(int $first = 0, int $second = 12): self
    {
        $this->expression = "0 {$first},{$second} * * *";
        return $this;
    }

    public function everyTwoDays(): self
    {
        $this->expression = '0 0 */2 * *';
        return $this;
    }

    public function everyThreeDays(): self
    {
        $this->expression = '0 0 */3 * *';
        return $this;
    }
    
    public function weekly(): self
    {
        $this->expression = '0 0 * * 0';
        return $this;
    }

    public function weeklyOn(int $day = 0, string $time = '0:00'): self
    {
        $parts = explode(':', $time);
        $hour = $parts[0] ?? '0';
        $minute = $parts[1] ?? '0';
        $this->expression = "{$minute} {$hour} * * {$day}";
        return $this;
    }

    public function mondays(): self
    {
        return $this->weeklyOn(1);
    }

    public function tuesdays(): self
    {
        return $this->weeklyOn(2);
    }

    public function wednesdays(): self
    {
        return $this->weeklyOn(3);
    }

    public function thursdays(): self
    {
        return $this->weeklyOn(4);
    }

    public function fridays(): self
    {
        return $this->weeklyOn(5);
    }

    public function saturdays(): self
    {
        return $this->weeklyOn(6);
    }

    public function sundays(): self
    {
        return $this->weeklyOn(0);
    }
    
    public function monthly(): self
    {
        $this->expression = '0 0 1 * *';
        return $this;
    }

    public function monthlyOn(int $day = 1, string $time = '0:00'): self
    {
        $parts = explode(':', $time);
        $hour = $parts[0] ?? '0';
        $minute = $parts[1] ?? '0';
        $this->expression = "{$minute} {$hour} {$day} * *";
        return $this;
    }

    public function quarterly(): self
    {
        $this->expression = '0 0 1 */3 *';
        return $this;
    }

    public function yearly(): self
    {
        $this->expression = '0 0 1 1 *';
        return $this;
    }

    public function at(string $time): self
    {
        return $this->dailyAt($time);
    }
    
    public function cron(string $expression): self
    {
        $this->expression = $expression;
        return $this;
    }
    
    public function withoutOverlapping(): self
    {
        $this->withoutOverlap = true;
        return $this;
    }

    public function runInBackground(): self
    {
        $this->background = true;
        return $this;
    }

    public function retry(int $times): self
    {
        $this->retry = $times;
        return $this;
    }

    public function queue(): self
    {
        $this->queue = true;
        return $this;
    }
}

