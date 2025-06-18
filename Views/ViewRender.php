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

    private static function resolveIncludes($output)
    {
        $output = preg_replace('/<!--(.*?)-->/', '', $output);
        while (preg_match('/@include\(\s*[\'"](.+?)[\'"]\s*\)/', $output, $matches)) {
            $included_content = self::resolveIncludes(
                self::getContentView(
                    view_root($matches[1]),
                    $matches[1]
                )
            );
            $output = str_replace($matches[0], $included_content, $output);
        }
        return $output;
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
        if (time() - filemtime($viewParse) > 300) { // 5 minutes
            self::$binding['view_parse'] = null;
            return false;
        }
        extract(self::$binding['data']);
        ob_start();
        require $viewParse;
        self::$binding['view_parse'] = ob_get_clean();
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
    // extend view
    /**
     * Inherit layout
     * @param string $template
     * @return static
     */
    public static function inherit($template)
    {
        self::$binding['parent_layout'] = $template;
        return self::instance();
    }

    /**
     * Start section
     * @param string $section
     * @return void
     */
    public static function start($section)
    {
        self::$binding['current_section'] = $section;
        ob_start();
    }

    /**
     * End section
     * @return void
     */
    public static function stop() {
        if (self::$binding['current_section']) {
            self::$binding['sections'][self::$binding['current_section']] = ob_get_clean();
            self::$binding['current_section'] = null;
        }
    }

    /**
     * @param string $section
     * @return void
     */
    public static function attach($section)
    {
        if (isset(self::$binding['sections'][$section])) {
            self::$binding['sections'] .= ob_get_clean();
        } else {
            self::$binding['sections'] = ob_get_clean();
        }
        self::$binding['current_section'] = null;
    }

    /**
     * @param string $section
     * @return string
     */
    public static function yield($section)
    {
        return self::$binding['sections'][$section] ?? '';
    }
}