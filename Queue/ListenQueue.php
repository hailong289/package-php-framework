<?php

namespace Hola\Queue;

class ListenQueue {
    private static $instance = null;
    private $listeners = [];
    public $bindings = [];

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new ListenQueue();
        }
        return self::$instance;
    }

    public function trigger($event, ...$args) {
        if (isset($this->listeners[$event])) {
            $args = array_merge([$this], $args);
            call_user_func($this->listeners[$event], ...$args);
        }
    }

    public function failed(callable $callback) {
        $this->listeners['failed'] = $callback;
    }

    public function done(callable $callback) {
        $this->listeners['success'] = $callback;
    }

    public function isFailed()
    {
        return !empty($this->listeners['failed']);
    }

    public function isDone()
    {
        return !empty($this->listeners['success']);
    }

    public function isBindingConnection()
    {
        return !empty($this->bindings['connection']);
    }

    public function isBindingConnectionType()
    {
        return !empty($this->bindings['connection_type']);
    }

}