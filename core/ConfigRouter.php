<?php
namespace System\Core;
class ConfigRouter extends Router {
    private $default = 'web';
    private $pathArray = [];

    public function __construct()
    {
        require_once __DIR__ROOT."/router/$this->default.php";
    }

    public function loadFile($name) {
        if(file_exists(__DIR__ROOT."/router/$name.php")) {
            require_once __DIR__ROOT."/router/$name.php";
        } else {
            http_response_code(500);
            throw new \RuntimeException("File $name in router does not exit", 500);
            return;
        }
        self::$path_load_file = '';
        return $this;
    }

    public function add($name) {
        if(is_array($name)) {
            $this->pathArray = $name;
            return $this;
        }
        self::$path_load_file = '/'. $name;
        return $this;
    }

    public function work() {
        if(empty($this->pathArray)) {
            throw new \RuntimeException('Name function add() is not null', 500);
            return;
        }

        foreach ($this->pathArray as $name=>$fileName) {
            self::$path_load_file = '/'. $name;
            $this->loadFile($fileName);
        }
    }
}