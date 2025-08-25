<?php

namespace Hola\Views;
use Hola\Exceptions\AppException;

class ViewRender {
    private static ViewRender|null $instance = null;
    private static $binding = [
        'view_root' => null, // path root
        'view_parse' => null, // path has render
        'parent_layout' => null, // path layout
        'current_section' => null, // current section
        'sections' => [], // path section
        'html' => [], // file html,
        'data' => [], // data for view
    ];

    public static function instance(): ViewRender
    {
        if (is_null(self::$instance)) {
            self::$instance = new ViewRender();
        }
        return self::$instance;
    }

    public static function cacheFileHtml(array $names)
    {
        self::$binding['html'] = $names;
        return self::instance();
    }

    public static function render($view, $data = []) {
        self::resolveViewRoot($view);
        self::resolveData($data);
        self::resolveViewHasParse($view);

        if (!empty(self::$binding['view_parse'])) {
            return self::$binding['view_parse'];
        }

        return self::resolveRenderHtml(
            $view,
            function () use ($view) {
                $template = self::getContentView(self::$binding['view_root'], $view);
                // If the view has a parent layout, we need to parse it
                $template = (new ViewCompiler())->compile($template);
                return (new Parser($template))->parse($view);
            }
        );
    }

    public static function renderXml($data = [])
    {
        self::$binding['data'] = $data;
        $xml = new \SimpleXMLElement('<root/>');
        self::resolveArrayToXml($xml);
        return $xml;
    }

    private static function getContentView($view, $name)
    {
        if (in_array($name, self::$binding['html'])) {
            ob_start();
            require($view);
            return ob_get_clean();
        }
        return file_get_contents($view);
    }

    private static function resolveRenderHtml($path_name, $callback)
    {
        if ($path_name === 'error.index') {
            return self::getDefaultViewError();
        }
        $view_render = self::encryptionViewRender($path_name);
        createFolder(getFolder($view_render));
        file_put_contents($view_render, $callback());
        $outputHtml = self::includeViewWithVars($view_render);
        if (in_array($path_name, self::$binding['html'])) {
            file_put_contents($view_render, $outputHtml);
        }
        return $outputHtml;
    }

    private static function resolveArrayToXml(&$xml) {
        foreach (self::$binding['data'] as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                self::resolveArrayToXml($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

    protected static function resolveData($data)
    {
        self::$binding['data'] = $data;
    }

    protected static function resolveViewRoot($view)
    {
        $view_root = view_root($view);
        if(!file_exists($view_root)){
            if ($view === 'error.index') {
                self::$binding['view_root'] = __DIR__ . "/pages/error.view.php";
                return false;
            }
            throw new AppException("File App/Views/$view.view.php does not exist", 500);
        }
        self::$binding['view_root'] = $view_root;
        return false;
    }

    protected static function resolveViewHasParse($path_name)
    {
        $viewParse = self::encryptionViewRender($path_name);

        if (!file_exists($viewParse)) {
            return false;
        }

        if (in_array($path_name, self::$binding['html'])) {
            self::$binding['view_parse'] = file_get_contents($viewParse);
            return true;
        }

        // Check file modify time > 5 minutes
        $lastModified = filemtime($viewParse);
        if ($lastModified === false || (time() - $lastModified) > 300) {
            self::$binding['view_parse'] = null;
            return false;
        }

        self::$binding['view_parse'] = self::includeViewWithVars($viewParse);
        return true;
    }

    protected static function encryptionViewRender($name)
    {
        $view = substr(self::$binding['view_root'], strpos(self::$binding['view_root'], 'Views'));
        $extension = in_array($name, self::$binding['html']) ? '.html' : '.php';
        $encryption = md5($view);
        return __DIR__ROOT . "/storage/render/{$encryption}{$extension}";
    }

    protected static function getDefaultViewError()
    {
        extract(self::$binding['data']);
        ob_start();
        require(self::$binding['view_root']);
        return ob_get_clean();
    }

    protected static function includeViewWithVars($view_render) {
        extract(self::$binding['data']);
        ob_start();
        require $view_render;
        return ob_get_clean();
    }

    public static function include($view_root)
    {
        if (file_exists($view_root)) {
            extract(self::$binding['data']);
            ob_start();
            include $view_root;
            return ob_get_clean();
        }
        return '';
    }
}