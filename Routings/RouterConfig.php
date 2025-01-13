<?php
namespace Hola\Routings;
class RouterConfig extends Router {
    private $default = 'web';
    private $pathArray = [];
    private static $instance = null;

    public static function init()
    {
        if (self::$instance === null) {
            self::$instance = new RouterConfig();
        }
        return self::$instance;
    }

    public function loadFile($name) {
        if(file_exists(__DIR__ROOT."/router/$name.php")) {
            require_once __DIR__ROOT."/router/$name.php";
        } else {
            http_response_code(500);
            throw new \RuntimeException("File $name in router does not exit", 500);
        }
        return $this;
    }

    public function add($name) {
        if(is_array($name)) {
            $this->pathArray = $name;
            return $this;
        }
        return $this;
    }

    public function work() {
        if(empty($this->pathArray)) {
            throw new \RuntimeException('Name function add() is not null', 500);
        }
        foreach ($this->pathArray as $value) {
            if (!isset($value['url']) || !isset($value['file'])) {
                throw new \RuntimeException('Url and file is not null', 500);
            }
            if (!empty($value['url'])) {
                self::mainPath($value['url'] . '/');
            }
            $this->loadFile($value['file']);
        }
    }
}