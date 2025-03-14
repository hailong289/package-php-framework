<?php

namespace Hola\Core;

use Hola\Exceptions\AppException;

class ViewRender {
    private static ViewRender|null $instance = null;
    private static $directive = [];
    private static $file_html = [];

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

    public static function hasCacheFileHtml($name)
    {
        return in_array($name, self::$file_html) && file_exists(self::viewRenderByName($name, '.html'));
    }

    private static function defaultDirective()
    {
        $directive = array_merge([
            ['regex' => '/{{(.+)}}/', 'render' => '<?=$1?>'],
            ['regex' => '/@php(.?)@endphp/', 'render' => '<?php $1 ?>'],
            [
                'regex' => [
                    '/@foreach\((.*?)\)/s',
                    '/@endforeach/'
                ],
                'render' => [
                    '<?php foreach($1): ?>',
                    '<?php endforeach; ?>'
                ]
            ],
            [
                'regex' => [
                    '/@for\((.*?)\)/s',
                    '/@endfor/'
                ],
                'render' => [
                    '<?php for($1): ?>',
                    '<?php endfor; ?>'
                ]
            ],
            [
                'regex' => [
                    '/@if\((.*?)\)/s',
                    '/@elseif\((.*?)\)/s',
                    '/@else\((.*?)\)/s',
                    '/@endif/'
                ],
                'render' => [
                    '<?php if($1): ?>',
                    '<?php elseif($1): ?>',
                    '<?php else($1): ?>',
                    '<?php endif; ?>',
                ]
            ],
            [
                'regex' => [
                    '/@switch\((.*?)\)/s',
                    '/@case\((.*?)\)/s',
                    '/@break/',
                    '/@default/',
                    '/@endswitch/'
                ],
                'render' => [
                    '<?php switch ($1): ?>',
                    '<?php case ($1): ?>',
                    '<?php break; ?>',
                    '<?php default: ?>',
                    '<?php endswitch; ?>',
                ]
            ],
            ['regex' => '/@class\((.*?)\)/', 'render' => 'class="<?=implode(" ",$1)?>"'],
            ['regex' => '/@style\((.*?)\)/', 'render' => 'style="<?=implode(" ",$1)?>"'],
            ['regex' => '/@checked\((.*?)\)/', 'render' => 'checked="$1"'],
            ['regex' => '/@selected\((.*?)\)/', 'render' => 'selected="$1"'],
            ['regex' => '/@disabled\((.*?)\)/', 'render' => 'disabled="$1"'],
            ['regex' => '/@readonly\((.*?)\)/', 'render' => 'readonly="$1"'],
        ], self::$directive);
        return $directive;
    }

    public static function render($view, $data = []) {
        $path_view = self::resolveFileView($view);
        $view_render = self::getViewRender($path_view, $view);
        if (file_exists($view_render)) {
            ob_start();
            extract($data, EXTR_SKIP);
            if (in_array($view, self::$file_html)) {
                require $view_render;
                return ob_get_clean();
            }
            require $view_render;
            return ob_get_clean();
        }

        return self::resolveRenderHtml(
            $path_view,
            $view,
            function () use ($path_view, $view, $data) {
                return self::resolveDirective(
                    self::resolveIncludes(
                        self::resolveViewContent($path_view, $view),
                        $data
                    )
                );
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

    public static function directive($directive, $fun)
    {
        $reflection = new \ReflectionFunction($fun);
        $params = $reflection->getParameters();
        if (!empty($params)) {
            $string_regex = "";
            $args = [];
            foreach ($params as $key=>$param) {
                $number = $key + 1;
                $string_regex = $string_regex ? $string_regex . ",(.*?)" : "(.*?)";
                $args[] = "$$number";
            }
            $render = $fun(...$args);
        } else {
            $string_regex = "(.*?)";
            $render = $fun();
        }
        self::$directive[] = [
            'regex' => "/@$directive\($string_regex\)/",
            'render' => $render,
        ];
        return self::instance();
    }

    private static function resolveFileView($view)
    {
        if(!file_exists(view_root($view))){
            if ($view === 'error.index') {
                return dirname(__DIR__, 1) . "/view/error.view.php";
            }
            throw new AppException("File App/Views/$view.view.php does not exist", 500);
        }
        return view_root($view);
    }

    private static function resolveDirective($output)
    {
        foreach (self::defaultDirective() as $directive) {
            $output = preg_replace($directive['regex'], $directive['render'], $output);
        }
        return $output;
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

    private static function resolveRenderHtml($view_current, $name, $callback, $data = [])
    {
        ob_start();
        extract($data, EXTR_SKIP);
        if ($name === 'error.index') {
            require($view_current);
            return ob_get_clean();
        }
        $view_render = self::getViewRender($view_current, $name);
        createFolder(getFolder($view_render));
        file_put_contents($view_render, $callback());
        require_once $view_render;
        return ob_get_clean();
    }

    private static function getViewRender($view_current, $name)
    {
        $view = substr($view_current, strpos($view_current, 'Views'));
        $extension = in_array($name, self::$file_html) ? '.html' : '.php';
        $view_render = str_replace('.view.php', $extension,  __DIR__ROOT . "/storage/render/$view");
        return $view_render;
    }

    private static function viewRenderByName($name, $ext = '.php')
    {
        $view = preg_replace('/([.]+)/', '/' , $name);
        return __DIR__ROOT . "/storage/render/Views/{$view}{$ext}";
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
}