<?php

namespace Hola\Core;

class ViewRender {
    private static $instance = null;
    private static $directive = [];

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new ViewRender();
        }
        return self::$instance;
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
        $view = self::resloveFileView($view);
        $output = self::resloveViewContent($view);
        $output = self::resloveIncludes($output);
        $output = self::resloveDirective($output);
        return self::resloveRenderHtml($view, $output, $data);
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

    private static function resloveFileView($view)
    {
        $file_view = view_root($view);
        if(!file_exists($file_view)){
            if ($view === 'error.index') {
                $path = dirname(__DIR__, 1);
                $file_view = "$path/view/error.view.php";
                return $file_view;
            }
            throw new \RuntimeException("File App/Views/$view.view.php does not exist", 500);
        }
        return $file_view;
    }

    private static function resloveDirective($output)
    {
        $list_directive = self::defaultDirective();
        foreach ($list_directive as $directive) {
            $output = preg_replace($directive['regex'], $directive['render'], $output);
        }
        return $output;
    }

    private static function resloveViewContent($view)
    {
        ob_start();
        require_once $view;
        $output = ob_get_clean();
        return $output;
    }

    private static function resloveIncludes($output)
    {
        while (preg_match('/@include\(\s*[\'"](.+?)[\'"]\s*\)/', $output, $matches)) {
            $includedView = view_root($matches[1]);
            $includedContent = self::resloveViewContent($includedView);
            $includedContent = self::resloveIncludes($includedContent);
            $output = str_replace($matches[0], $includedContent, $output);
        }
        return $output;
    }

    private static function resloveRenderHtml($viewCurrent, $output, $data)
    {
        extract($data);
        $folder = __DIR__ROOT . '/storage/render';
        $startPos = strpos($viewCurrent, 'Views');
        $view = substr($viewCurrent, $startPos);
        $view_render = "$folder/$view";
        $view_render = str_replace('.view.php', '.php', $view_render);
        if (file_exists($view_render)) {
            require_once $view_render;
            return $view_render;
        }
        createFolder(getFolder($view_render));
        file_put_contents($view_render, $output);
        require_once $view_render;
        return $view_render;
    }

}