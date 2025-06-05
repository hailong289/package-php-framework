<?php
namespace Hola\Container;
use Hola\Core\ConfigApp;

class RegisterLoad
{
    private ConfigApp|null $configApp = null;
    private $bind = [
        'config' => [],
        'include' => [],
        'has_cache' => false,
    ];

    public function __construct() {
        $this->configApp = ConfigApp::init();
        $this->bind['config'] = cache()
            ->file()
            ->setPath('storage/cache')
            ->get('configs') ?? [];
        $this->bind['has_cache'] = !empty($this->bind['config']);
    }

    /**
     * Register file
     *
     * @param string $name
     * @return $this
     */
    public function registerFile($name)
    {
        $pathName = __DIR__ROOT . "/$name.php";
        if (file_exists($pathName)) {
            $this->bind['include'][] = $pathName;
        }
        return $this;
    }


    /**
     * Register session
     *
     * @return $this
     */
    public function registerSession()
    {
        session_start();
        return $this;
    }


    /**
     * Register router
     *
     * @return $this
     */
    public function routerWorkLoad()
    {
        $pathName = __DIR__ROOT . "/router/index.php";
        if (file_exists($pathName)) {
            $this->bind['include'][] = $pathName;
        }
        return $this;
    }

    public function loadEnvironment($file = '.env')
    {
        if ($this->bind['has_cache']) {
            return $this;
        }
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ROOT, $file);
        $dotenv->load();
        $env_arr = [];
        foreach ($_ENV as $key => $value) {
            if (!isset($env_arr[$key])) {
                $env_arr[$key] = $value;
            }
        }
        $env_arr = array_merge($env_arr, getenv());
        if (!isset($env_arr['PROJECT_KEY'])) {
            die('PROJECT_KEY is not defined in environment');
        }
        $this->bind['config']['environment'] = $env_arr;
    }

    /**
     * load config
     *
     * @return $this
     */
    public function loadConfig()
    {
        if ($this->bind['has_cache']) {
            return $this;
        }
        $config = rglob(__DIR__ROOT ."/config/*.php") ?? [];
        $config_arr = [];
        foreach ($config as $item) {
            if (file_exists($item)) {
                $items = explode("/", $item);
                $end = end($items);
                $end = str_replace('.php', '', $end);
                $this->bind['config'][$end] = require($item);
            }
        }
    }

    /**
     * load language
     *
     * @return $this
     */
    public function loadLanguage()
    {
        if ($this->bind['has_cache']) {
            return $this;
        }
        $language = rglob(__DIR__ROOT ."/language/*.php") ?? [];
        foreach ($language as $item) {
            if (file_exists($item)) {
                $items = explode("/", $item);
                $end = end($items);
                $end = str_replace('.php', '', $end);
                $this->bind['config']['language'][$end] = require($item);
            }
        }
    }

    /**
     * load timezone
     *
     * @param string $timezone
     * @return $this
     */
    public function loadTimeZone($timezone = null)
    {
        $timezone = $timezone ?? conval('TIMEZONE', 'Asia/Ho_Chi_Minh');
        date_default_timezone_set($timezone);
        return $this;
    }

    /**
     * init app
     *
     * @return $this
     */
    public function initApp()
    {
        $pathName = __DIR__ROOT . "/App/App.php";
        if (file_exists($pathName)) {
            $this->bind['include'][] = $pathName;
        }
        $this->resolveInclude();
        $this->resolveConfig();
        return $this;
    }

    /**
     * init CLI
     *
     * @return void
     */
    public function initCLI()
    {
        $this->loadEnvironment();
        $this->loadConfig();
        $this->loadTimeZone();
        $this->registerFolder(['database']);
        $this->resolveInclude();
        $this->resolveConfig();
        return $this;
    }

    /**
     * Register folder
     *
     * @param string|array $pathName
     * @return $this
     */
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

    /**
     * load files
     *
     * @param array $files
     * @return void
     */
    private function loadFiles($files)
    {
        foreach ($files as $item) {
            if (file_exists($item)) {
                $this->bind['include'][] = $item;
            } else {
                throw new \Exception("File $item does not exist");
            }
        }
    }


    private function resolveInclude()
    {
        foreach ($this->bind['include'] as $item) {
            require_once $item;
        }
    }

    private function resolveConfig()
    {
        if (!$this->bind['has_cache']) {
            cache()->file()
                ->setPath('storage/cache')
                ->store('configs', $this->bind['config']);
        }
        foreach ($this->bind['config'] as $key => $item) {
            $this->configApp->create($key, $item);
        }
    }

}