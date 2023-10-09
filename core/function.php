<?php

if(!function_exists('startsWith')){
    function startsWith( $haystack, $needle ) {
        $length = strlen( $needle );
        return substr( $haystack, 0, $length ) === $needle;
    }
}


if(!function_exists('endsWith')){
    function endsWith( $haystack, $needle ) {
        $length = strlen( $needle );
        if( !$length ) {
            return true;
        }
        return substr( $haystack, -$length ) === $needle;
    }
}

if(!function_exists('str_slug')){
    function str_slug($str, $delimiter = '-')
    {
        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;
    }
}

if(!function_exists('path_root')){
    function path_root($url)
    {
        return str_replace('\\','/',$_SERVER["DOCUMENT_ROOT"] ."/" . $url);
    }
}

if(!function_exists('view_root')){
    function view_root($view)
    {
        return __DIR__ROOT . '/app/views/'.$view.'.view.php';
    }
}

if(!function_exists('log_debug')){
    function log_debug(...$args) {
        echo "<pre>";
        print_r($args);
        echo "</pre>";
        exit();
    }
}

if(!function_exists('log_write')){
    function log_write($e) {
        $date = "\n\n[".date('Y-m-d H:i:s')."]: ";
        file_put_contents(__DIR__ROOT .'/storage/debug.log',$date . $e, FILE_APPEND);
    }
}