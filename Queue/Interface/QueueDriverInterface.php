<?php
namespace Hola\Queue\Interface;
interface QueueDriverInterface {
    public function enqueue(string $connection, string $queue, string $data): void;
}