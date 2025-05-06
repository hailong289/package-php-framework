<?php

namespace Hola\Views;

use Hola\Exceptions\AppException;

class Parser {
    protected $template;
    protected $template_name = null;
    protected $rules = [
        ['regex' => '/\{{\s*(.*?)\s*\}}/s', 'render' => 'callback', 'func' => 'variable'],
        [
            'regex' => '/@php\s*\{/',
            'render' => '<?php <<PUSH:php>>'
        ],
        [
            'regex' => [
                '/@if\s*\((.*?)\)\s*\{/',
                '/@elseif\s*\((.*?)\)\s*\{/',
                '/@else\s*\{/'
            ],
            'render' => [
                '<?php if ($1): ?> <<PUSH:if>>',
                '<?php elseif ($1): ?>',
                '<?php else: ?>',
            ]
        ],
        [
            'regex' => '/@foreach\s*\((.*?)\)\s*\{/',
            'render' => '<?php foreach ($1): ?> <<PUSH:foreach>>'
        ],
        [
            'regex' => '/@for\s*\((.*?)\)\s*\{/',
            'render' => '<?php for ($1): ?> <<PUSH:for>>'
        ],

        [
            'regex' => [
                '/@switch\s*\((.*?)\)\s*\{/',
                '/@case\s*\((.*?)\)\s*\{/',
                '/@default\s*\{/'
            ],
            'render' => [
                '<?php switch ($1): ?> <<PUSH:switch>>',
                '<?php case $1: ?>',
                '<?php default: ?>'
            ]
        ],
        [
            'regex' => '/@empty\s*\((.*?)\)\s*\{/',
            'render' => '<?php if(empty($1)): ?>'
        ],
        [
            'regex' => '/@notEmpty\s*\((.*?)\)\s*\{/',
            'render' => '<?php if(!empty($1)): ?>'
        ],
        [
            'regex' => '/(?<!<\?php)(?<!\?>)\}/',
            'render' => '<<POP>>'
        ],
        ['regex' => '/@class\((.*?)\)/', 'render' => 'class="<?=implode(" ",$1)?>"'],
        ['regex' => '/@style\((.*?)\)/', 'render' => 'style="<?=implode(" ",$1)?>"'],
        ['regex' => '/@checked\((.*?)\)/', 'render' => '<?=($1) ? "checked" : ""?>'],
        ['regex' => '/@selected\((.*?)\)/', 'render' => '<?=($1) ? "selected" : ""?>'],
        ['regex' => '/@disabled\((.*?)\)/', 'render' => '<?=($1) ? "disabled" : ""?>'],
        ['regex' => '/@readonly\((.*?)\)/', 'render' => '<?=($1) ? "readonly" : ""?>'],
        ['regex' => '/@(\w+)\s*=\s*"([^"]+)"/', 'render' => 'callback', 'func' => 'bindEventJS']
    ];

    public function __construct($template) {
        $this->template = $template;
    }

    public function parse($view) {
        $this->template_name = $view;

        foreach ($this->rules as $rule) {
            $regex = $rule['regex'];
            $render = $rule['render'];
            if ($render === 'callback') {
                $this->template = preg_replace_callback($regex, function ($matches) use ($rule) {
                    return $this->{$rule['func']}($matches);
                }, $this->template);
            } else {
                $this->template = preg_replace($regex, $render, $this->template);
            }
        }

        $lines = explode("\n", $this->template);
        $stack = [];
        $output = '';

        foreach ($lines as $line) {
            if (strpos($line, '<<PUSH:') !== false) {
                if (preg_match('/<<PUSH:(\w+)>>/', $line, $match)) {
                    $stack[] = $match[1];
                    $line = str_replace($match[0], '', $line);
                }
            } elseif (strpos($line, '<<POP>>') !== false) {
                $type = array_pop($stack);
                $phpEnd = match ($type) {
                    'if' => '<?php endif; ?>',
                    'foreach' => '<?php endforeach; ?>',
                    'for' => '<?php endfor; ?>',
                    'switch' => '<?php endswitch; ?>',
                    'php' => '?>',
                    default => '}'
                };
                $line = str_replace('<<POP>>', $phpEnd, $line);
            }

            $output .= $line . "\n";
        }
        return $output;
    }

    private function variable($matches)
    {
        $match = trim($matches[1]);
        $expression = explode('|', $match);
        $value = trim(array_shift($expression));
        $pipe = trim($expression[0] ?? '');
        if (!empty($pipe)) {
            $pipeHandler = $this->resolvePipe($pipe, $value);
            return "<?= $pipeHandler ?>";
        }

        return "<?= $value ?>";
    }

    private function bindEventJS($matches)
    {
        $event = strtolower($matches[1]);   // @Click => click
        $handler = $matches[2];             // functionName

        $eventMapping = [
            'click', 'input', 'change', 'submit',
            'mouseover', 'mouseout', 'mouseenter', 'mouseleave',
            'keydown', 'keyup', 'focus', 'blur'
        ];

        if (in_array($event, $eventMapping)) {
            $handler = preg_replace_callback('/\{{\s*(.*?)\s*\}}/s', function ($m) {
                $phpVar = $m[1];
                return "'<?= $phpVar ?>'";
            }, $handler);

            if (strpos($handler, '(') === false) {
                $handler .= '()';
            }
            return "on$event" . '="' . $handler . '"';
        }

        return $matches[0];
    }

    private function resolvePipe(string $pipe, string $value): string
    {
        $valuePipe = explode(':', $pipe);
        $pipe = array_shift($valuePipe);
        $pipeClassName = ucfirst($pipe) . 'Pipe';
        $fullClass = "\\App\\Pipes\\{$pipeClassName}";
        $fullClassDefault = "\\Hola\\Views\\Pipes\\{$pipeClassName}";
        $getPipe = class_exists($fullClass) ? $fullClass : $fullClassDefault;

        if (!class_exists($getPipe)) {
            throw new AppException("Pipe '{$pipe}' does not exist in view {$this->template_name}");
        }

        $funcHandle = 'handle';
        if (!empty($valuePipe)) {
            $funcHandle .= "($value";
            foreach ($valuePipe as $v) {
                $funcHandle .= ", ". trim($v);
            }
            $funcHandle .= ")";
        } else {
            $funcHandle .= "($value)";
        }

        return "(new {$getPipe}())->$funcHandle";
    }

}
