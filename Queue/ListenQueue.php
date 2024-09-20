<?php

namespace Hola\Queue;

class ListenQueue {
    private static $instance = null;
    private $listeners = [];

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new ListenQueue();
        }
        return self::$instance;
    }

    public function trigger($event, ...$args) {
        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $callback) {
                call_user_func($callback, ...$args);
            }
        }
    }

    public function failed(callable $callback) {
        $this->listeners['failed'][] = $callback;
    }
}