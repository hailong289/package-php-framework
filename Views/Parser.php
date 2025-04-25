<?php

namespace Hola\Views;

use Hola\Exceptions\AppException;

class Parser {
    protected $template;
    protected $template_name = null;
    protected $rules = [
        ['regex' => '/\{\s*\$(\w+)\s*(?:\|\s*(\w+))?\s*\}/', 'render' => 'callback', 'func' => 'variable'],
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
            'regex' => '/\}/',
            'render' => '<<POP>>'
        ],
        ['regex' => '/@class\((.*?)\)/', 'render' => 'class="<?=implode(" ",$1)?>"'],
        ['regex' => '/@style\((.*?)\)/', 'render' => 'style="<?=implode(" ",$1)?>"'],
        ['regex' => '/@checked\((.*?)\)/', 'render' => 'checked="$1"'],
        ['regex' => '/@selected\((.*?)\)/', 'render' => 'selected="$1"'],
        ['regex' => '/@disabled\((.*?)\)/', 'render' => 'disabled="$1"'],
        ['regex' => '/@readonly\((.*?)\)/', 'render' => 'readonly="$1"'],
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
                    default => ''
                };
                $line = str_replace('<<POP>>', $phpEnd, $line);
            }

            $output .= $line . "\n";
        }
        return $output;
    }

    private function variable($matches)
    {
        $expression = trim($matches[1]);
        $value = "\$$expression";
        $pipe = trim($matches[2] ?? '');

        if (!empty($pipe)) {
            $pipeHandler = $this->resolvePipe($pipe, $value);
            return "<?= $pipeHandler ?>";
        }

        return "<?= $value ?>";
    }

    private function resolvePipe(string $pipe, string $value): string
    {
        $dirPipes = __DIR__ROOT . '/App/Pipes';
        $pipeClassName = ucfirst($pipe) . 'Pipe';
        $fullClass = "\\App\\Pipes\\{$pipeClassName}";
        $fullClassDefault = "\\Hola\\Views\\Pipes\\{$pipeClassName}";
        $getPipe = class_exists($fullClassDefault) ? $fullClassDefault : $fullClass;
      
        if (!class_exists($getPipe)) {
            throw new AppException("Pipe '{$pipe}' does not exist in view {$this->template_name}");
        }

        return "(new {$getPipe}())->handle({$value})";
    }
}
