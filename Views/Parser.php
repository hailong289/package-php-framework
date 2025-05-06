<?php

namespace Hola\Views;

use Hola\Exceptions\AppException;

class Parser {
    protected $template;
    protected $template_name = null;
    protected $rules = [
        ['regex' => '/\{{\s*(.*?)\s*\}}/s', 'render' => 'callback', 'func' => 'variable'],
        [
            'regex' => [
                '/@php\s*\{/',
                '/@if\s*\((.*?)\)\s*\{/',                     // Match @if ($condition) {
                '/@foreach\s*\((.*?)\)\s*\{/s',               // Match @foreach ($collection as $item) {
                '/@forelse\s*\(([^ ]+)(?:\s*(as\s*.+?))?\)\s*\{/s',               // Match @forelse ($collection as $item) {
                '/@for\s*\((.*?)\)\s*\{/',                    // Match @for ($i = 0; $i < 10; $i++) {
                '/@switch\s*\((.*?)\)\s*\{/s',                // Match @switch ($expression) {
                '/@case\s*\((.*?)\)\s*\{/',                       // Match @case (value)
                '/@break\s*/s',                               // Match @break
                '/@continue\s*/s',                            // Match @continue
                '/@default\s*\{/',                             // Match @default
                '/@empty\s*\{/s',                             // Match @empty {
                '/\}\s*@elseif\s*\((.*?)\)\s*\{/',            // Match } @elseif ($condition) {
                '/\}\s*@else\s*\{/',                          // Match } @else {
                '/(?<!<\?php)(?<!\?>)\}/'                     // Match closing braces }
            ],
            'render' => [
                '<?php <<PUSH:php>>',                       // Render PHP and pop from stack
                '<?php if ($1): ?> <<PUSH:if>>',             // Render if and push to stack
                '<?php foreach ($1): ?> <<PUSH:foreach>>',   // Render foreach and push to stack
                '<?php if (!empty($1)): ?>' . PHP_EOL . '<?php foreach ($1 $2): ?> <<PUSH:forelse>>',   // Render forelse and push to stack
                '<?php for ($1): ?> <<PUSH:for>>',           // Render for and push to stack
                '<?php switch ($1): case "anything": break; ?> <<PUSH:switch>>',     // Render switch and push to stack
                '<?php case $1: ?> <<PUSH:case>>',                         // Render case
                '<?php break; ?>'.PHP_EOL,                           // Render break
                '<?php continue; ?>'.PHP_EOL,                        // Render continue
                '<?php default:  ?> <<PUSH:default>>',                         // Render default
                '<?php else: ?> <<PUSH:empty>>', // Render empty and push to stack
                '<?php elseif ($1): ?>',                     // Render elseif
                '<?php else: ?>',                            // Render else
                '<<CLOSE>>'                                 // Render closing braces
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
                    $pushType = $match[1];
                    if ($pushType === 'forelse') {
                        $stack[] = 'if';
                        $stack[] = 'foreach';
                    } elseif ($pushType === 'empty') {
                        $prev = array_pop($stack);
                        if ($prev !== 'foreach') {
                            throw new AppException("Syntax error: @empty must follow @forelse (expecting 'foreach' on stack)");
                        }
                        $line = '<?php endforeach; ?>' . PHP_EOL . '<?php else: ?>';
                    } else {
                        $stack[] = $pushType;
                    }
                    $line = str_replace($match[0], '', $line);
                }
            } elseif (strpos($line, '<<CLOSE>>') !== false) {
                $type = array_pop($stack);
                $phpEnd = match ($type) {
                    'if' => '<?php endif; ?>',
                    'foreach' => '<?php endforeach; ?>',
                    'for' => '<?php endfor; ?>',
                    'switch' => '<?php endswitch; ?>',
                    'case', 'default' => '',
                    'php' => '?>',
                     default => ''
                };
                $line = str_replace('<<CLOSE>>', $phpEnd, $line);
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
