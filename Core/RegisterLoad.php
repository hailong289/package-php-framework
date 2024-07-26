<?php
namespace Hola\Core;
class RegisterLoad
{

    public function registerFile($name)
    {
        $pathName = __DIR__ROOT . "/$name.php";
        if (file_exists($pathName)) {
            require_once $pathName;
        }
        return $this;
    }

    public function registerSession()
    {
        session_start();
        return $this;
    }

    public function languageLoad($name = null)
    {
        $lang = $name ?? LANGUAGE;
        $pathName = __DIR__ROOT . "/language/$lang.php";
        if (file_exists($pathName)) {
            $GLOBALS['data_lang'] = require($pathName);
        }
        return $this;
    }

    public function routerWorkLoad()
    {
        $pathName = __DIR__ROOT . "/router/index.php";
        if (file_exists($pathName)) {
            require_once $pathName;
        }
        return $this;
    }

    public function initApp()
    {
        $pathName = __DIR__ROOT . "/App/App.php";
        if (file_exists($pathName)) {
            require_once $pathName;
        }
        return $this;
    }

    public function registerFolder($pathName)
    {
        if (is_array($pathName)) {
            foreach ($pathName as $name) {
                $link_path = __DIR__ROOT . "/$name";
                $files = rglob("$link_path/*.php") ?? [];
                $this->loadFiles($files);
            }
        } else {
            $link_path = __DIR__ROOT . "/$pathName";
            $files = rglob("$link_path/*.php") ?? [];
            $this->loadFiles($files);
        }
        return $this;
    }

    private function loadFiles($files)
    {
        foreach ($files as $item) {
            if (file_exists($item)) {
                require_once $item;
            } else {
                throw new \Exception("File $item does not exist");
            }
        }
    }


}