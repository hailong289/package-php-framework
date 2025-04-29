<?php

namespace Hola\Views;

use Hola\Exceptions\AppException;

class ViewRender {
    private static ViewRender|null $instance = null;
    private static $file_html = [];
    private static $binding = [
        'view_root' => null, // path root
        'view_parse' => null // path has render
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
        self::$file_html = $names;
        return self::instance();
    }

    public static function render($view, $data = []) {
        self::resolveViewRoot($view);
        self::resolveViewHasParse($view, $data);

        if (!empty(self::$binding['view_parse'])) {
            return self::$binding['view_parse'];
        }

        return self::resolveRenderHtml(
            $view,
            function () use ($view, $data) {
                $template = self::resolveIncludes(
                    self::resolveViewContent(self::$binding['view_root'], $view),
                    $data
                );
                return (new Parser($template))->parse($view);
            },
            $data
        );
    }

    public static function renderXml($data = [])
    {
        $xml = new \SimpleXMLElement('<root/>');
        self::resolveArrayToXml($data, $xml);
        return $xml;
    }

    private static function resolveViewRoot($view)
    {
        if(!file_exists(view_root($view))){
            if ($view === 'error.index') {
                self::$binding['view_root'] = __DIR__ . "/pages/error.view.php";
                return false;
            }
            throw new AppException("File App/Views/$view.view.php does not exist", 500);
        }
        self::$binding['view_root'] = view_root($view);
        return false;
    }

    private static function resolveViewContent($view, $name)
    {
        if (in_array($name, self::$file_html)) {
            ob_start();
            require($view);
            return ob_get_clean();
        }
        return file_get_contents($view);
    }

    private static function resolveIncludes($output)
    {
        $output = preg_replace('/<!--(.*?)-->/', '', $output);
        while (preg_match('/@include\(\s*[\'"](.+?)[\'"]\s*\)/', $output, $matches)) {
            $included_content = self::resolveIncludes(
                self::resolveViewContent(
                    view_root($matches[1]),
                    $matches[1]
                )
            );
            $output = str_replace($matches[0], $included_content, $output);
        }
        return $output;
    }

    private static function resolveRenderHtml($path_name, $callback, $data = [])
    {
        if ($path_name === 'error.index') {
            return self::getDefaultViewError($data);
        }
        $view_render = self::encryptionViewRender($path_name);
        createFolder(getFolder($view_render));
        file_put_contents($view_render, $callback());
        $outputHtml = self::includeViewWithVars($view_render, $data);
        if (in_array($path_name, self::$file_html)) {
            file_put_contents($view_render, $outputHtml);
        }
        return $outputHtml;
    }

    private static function encryptionViewRender($name)
    {
        $view = substr(self::$binding['view_root'], strpos(self::$binding['view_root'], 'Views'));
        $extension = in_array($name, self::$file_html) ? '.html' : '.php';
        $encryption = md5($view);
        return __DIR__ROOT . "/storage/render/{$encryption}{$extension}";
    }

    private static function resolveViewHasParse($path_name, $data)
    {
        $viewParse = self::encryptionViewRender($path_name);
        if (file_exists($viewParse)) {
            if (in_array($path_name, self::$file_html)) {
                self::$binding['view_parse'] = file_get_contents($viewParse);
            } else {
                if (time() - filemtime($viewParse) > 300) { // 5 minutes
                    self::$binding['view_parse'] = null;
                    return false;
                }
                extract($data);
                ob_start();
                require $viewParse;
                self::$binding['view_parse'] = ob_get_clean();
            }
        }
    }

    private static function resolveArrayToXml($data, &$xml) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                self::resolveArrayToXml($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

    private static function getDefaultViewError($data)
    {
        extract($data);
        ob_start();
        require(self::$binding['view_root']);
        return ob_get_clean();
    }

    private static function includeViewWithVars($view_render, $data) {
        extract($data);
        ob_start();
        require $view_render;
        return ob_get_clean();
    }
}