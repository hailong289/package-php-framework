<?php

namespace Hola\Views;

use Hola\Exceptions\AppException;

class ViewCompiler {
    private $trim_chars = " \t\n\r\0\x0B\"'";
    private $items = [
        '@inherit' => '',
        '@startContent' => [],
        '@yield' => [],
        '@include' => [],
        '@push' => [],
    ];

    private $compiledRegex = [
        [
            'regex' => "/@inherit\((.*?)\)/",
            'callback' => 'inheritView',
            'assign' => false
        ],
        [
            'regex' => "/@startContent\((.*?)\)(.*?)@stopContent/s",
            'callback' => 'captureSection',
            'assign' => true
        ],
        [
            'regex' => "/@yield\((.*?)\)/",
            'callback' => 'yieldView',
            'assign' => false
        ],
        [
            'regex' => "/@include\((.*?)\)/",
            'callback' => 'includeView',
            'assign' => false
        ],
    ];

    public function compile($stringTemp)
    {
        foreach ($this->compiledRegex as $rule) {
            $stringTemp = preg_replace_callback(
                $rule['regex'],
                function ($matches) use ($rule) {
                    return $this->{$rule['callback']}($matches);
                },
                $stringTemp
            );
        }
        return $stringTemp;
    }

    private function inheritView($matches)
    {
        $viewName = trim($matches[1], $this->trim_chars);
        return file_get_contents(view_root($viewName));
    }

    private function captureSection($matches)
    {
        $name = trim($matches[1], $this->trim_chars);
        $content = $matches[2]; // nội dung giữa start/stop
        $this->items['@startContent'][$name] = $content;
        return '';
    }

    private function yieldView($matches)
    {
        $yieldName = trim($matches[1], $this->trim_chars);
        if (isset($this->items['@startContent'][$yieldName])) {
            return $this->items['@startContent'][$yieldName];
        } else {
            throw new AppException("Yield section $yieldName not found", 500);
        }
    }
    
    private function includeView($matches)
    {
        $includeName = view_root(trim($matches[1], $this->trim_chars));
        if (file_exists($includeName)) {
            $content = $this->encryptIncludeView($includeName);
            $content_callback = preg_replace_callback(
                "/@include\((.*?)\)/",
                function ($matches) {
                    return $this->includeView($matches);
                },
                $content
            );
            return $content_callback;
        }
    }

    private function encryptIncludeView($includeName)
    {
        $view_encrypt = md5($includeName);
        $view_root = __DIR__ROOT . "/storage/render/$view_encrypt.php";
        if (!file_exists($view_root)) {
            $content_raw = file_get_contents($includeName);
            $content_compile = (new ViewCompiler())->compile($content_raw);
            $content_parse = (new Parser($content_compile))->parse($includeName);
            file_put_contents($view_root, $content_parse);
        } else {
            $lastModified = filemtime($view_root);
            if ($lastModified === false || (time() - $lastModified) > 300) {
                $content_raw = file_get_contents($includeName);
                $content_compile = (new ViewCompiler())->compile($content_raw);
                $content_parse = (new Parser($content_compile))->parse($includeName);
                file_put_contents($view_root, $content_parse);
            }
        }
        return "<?=\Hola\Views\ViewRender::include('$view_root')?>";
    }

}