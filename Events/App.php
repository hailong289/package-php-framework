<?php
namespace Hola\Events;
class AppEvents {
    /**
     * @var AppEvents|null $appEvents
     * The instance of the AppEvents class
     */
    public static AppEvents|null $appEvents = null;

    /**
     * @var string[] $events
     * The events that are registered in the application
     */
    public static $events = [];

    public static function start()
    {
        if (self::$appEvents == null) {
            self::$appEvents = new AppEvents();
        }
        return self::$appEvents;
    }

    public function defaultRegister()
    {
        $this->register('app.start');
    }

    /**
     * @return AppEvents
     * Get the instance of the AppEvents class
     */
    public function listenRequest(callable $callback)
    {
        $uuid = uniqid();
        return $this->register('app.request', $callback, $uuid)
            ->register('app.query', $callback, $uuid)
            ->register('app.response', $callback, $uuid);
    }

    /**
     * @return AppEvents
     * Get the instance of the AppEvents class
     */
    public function listenException(callable $callback)
    {
        return $this->register('app.exceptions', $callback);
    }

    /**
     * @param string $event
     * @param string $listener
     * Register an event and its listener
     */
    private function register(string $event, callable $listener, $uuid = null)
    {
        if (is_null($uuid)) {
            $uuid = uniqid();
        }
        self::$events[$event] = [$uuid => $listener];
        return $this;
    }

    /**
     * @param string $event
     * @param mixed $data
     * Trigger an event and call its listeners
     */
    public function trigger(string $event, array $data = [])
    {
        if (isset(self::$events[$event])) {
            foreach (self::$events[$event] as $uuid => $listener) {
                if (is_callable($listener)) {
                    $data['uuid'] = $uuid;
                    call_user_func($listener, $data);
                }
            }
        }
    }

    /**
     * @param string $event
     * @param mixed $data
     * Trigger an event and call its listeners
     */
    public function triggerOnce(string $event, mixed $data = null) {
        if (isset(self::$events[$event])) {
            foreach (self::$events[$event] as $key => $listener) {
                if (is_callable($listener)) {
                    call_user_func($listener, $data);
                    unset(self::$events[$event][$key]);
                }
            }
        }
    }

    /**
     * @param string $event
     * @return bool
     * Check if an event is registered
     */
    public function hasEvent(string $event): bool {
        return isset(self::$events[$event]);
    }

    /**
     * @param string $event
     * @return array
     * Get all listeners for an event
     */

    public function getListeners(string $event): array
    {
        return self::$events[$event] ?? [];
    }

    /**
     * @param string $event
     * @return void
     * Remove all listeners for an event
     */

    public function clearListeners(string $event): void
    {
        if (isset(self::$events[$event])) {
            unset(self::$events[$event]);
        }
    }

    /**
     * @return void
     * Clear all events and listeners
     */
    public function clearAll(): void
    {
        self::$events = [];
    }

}