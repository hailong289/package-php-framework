<?php
namespace Hola\Queue\Driver;
use Hola\Queue\Interface\QueueDriverInterface;
use Hola\Connection\RabbitMQ;

class RabbitMQQueueDriver implements QueueDriverInterface
{
    public $name;
    
    public function __construct() {
        $this->name = 'rabbitmq';
    }
    
    public function enqueue(string $connection, string $queue, string $data): void
    {
        $rabbitMQ = RabbitMQ::instance($connection);
        $channel = $rabbitMQ->channel();

        try {
            $channel = $rabbitMQ->channel();
            $channel->queue_declare($queue, false, true, false, false);
            $attributes = [
                'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type' => 'application/json',
            ];
            
            $msg = new \PhpAmqpLib\Message\AMQPMessage(
                $data,
                $attributes
            );
            $channel->basic_publish($msg, '', $queue);
        } finally {
            $channel->close();
            $rabbitMQ->close();
        }
    }
}