<?php

namespace Hola\Views;

class Parser {
    protected $template;
    protected $rules = [
        ['regex' => '/{(.+)}/', 'render' => '<?=$1?>'],
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
    ];

    public function __construct($template) {
        $this->template = $template;
    }
    
    public function parse() {
        foreach ($this->rules as $rule) {
            $this->template = preg_replace($rule['regex'], $rule['render'], $this->template);
        }
        return $this->template;
    }
}
