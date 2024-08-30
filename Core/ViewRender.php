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
            ['regex' => '/@include\((.*?)\)/', 'render' => '<?php include($1) ?>'],
        ], self::$directive);
        return $directive;
    }

    public static function render($fileView)
    {
       $directive = self::defaultDirective();
       $content = file_get_contents($fileView);
       foreach ($directive as $directive) {
           $content = preg_replace($directive['regex'], $directive['render'], $content);
       }
       return $content;
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

}