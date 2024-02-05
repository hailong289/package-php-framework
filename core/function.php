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

if(!function_exists('url')){
    function url($path = '')
    {
        return (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . $path;
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
        http_response_code(500);
        echo "<pre>";
        print_r($args);
        echo "</pre>";
        exit();
    }
}

if(!function_exists('log_write')){
    function log_write($e) {
        $date = "\n\n[".date('Y-m-d H:i:s')."]: ";
        if (!file_exists(__DIR__ROOT .'/storage')) {
            mkdir(__DIR__ROOT .'/storage', 0777, true);
        }
        file_put_contents(__DIR__ROOT .'/storage/debug.log',$date . $e, FILE_APPEND);
    }
}

if(!function_exists('get_view')){
    function get_view($name, $data = [])
    {
        if(isset($GLOBALS['share_date_view']) && count($GLOBALS['share_date_view'])) $data = array_merge($data, $GLOBALS['share_date_view']);
        extract($data);
        $view = preg_replace('/([.]+)/', '/' , $name);
        require_once __DIR__ROOT . '/app/views/'.$view.'.view.php';
    }
}

if(!function_exists('__')){
    function __($key, $data_key = [], $lang = '')
    {
        if(!empty($lang)) {
            $data = require(path_root("language/$lang.php"));
        } else {
            $data = $GLOBALS['data_lang'];
        }
        $convert = $data[$key] ?? $key;
        foreach ($data_key as $k=>$value) {
            $convert = str_replace("{{".$k."}}", $value, $convert);
        }
        return $convert;
    }
}

if(!function_exists('translate')){
    function translate($key, $data_key = [], $lang = '')
    {
        if(!empty($lang)) {
            $data = require(path_root("language/$lang.php"));
        } else {
            $data = $GLOBALS['data_lang'];
        }
        $convert = $data[$key] ?? $key;
        foreach ($data_key as $k=>$value) {
            $convert = str_replace("{{".$k."}}", $value, $convert);
        }
        return $convert;
    }
}

if(!function_exists('lang_has')){
    function lang_has($key)
    {
        $data = $GLOBALS['data_lang'];
        return isset($data[$key]);
    }
}
if(!function_exists('isDate')){
    function isDate($value)
    {
        if (!$value) {
            return false;
        }
        try {
            new \DateTime($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
if(!function_exists('convert_to_array')){
    function convert_to_array($value)
    {
        if (!is_object($value)) die('The convert_to_array function parameter is not object');
        return json_decode(json_encode($value), true);
    }
}

if(!function_exists('convert_to_object')){
    function convert_to_object($value)
    {
        if (!is_array($value)) die('The convert_to_object function parameter is not array');
        return json_decode(json_encode($value));
    }
}

if(!function_exists('config_env')){
    function config_env($value, $default = '')
    {
        return constant($value) ?? $default;
    }
}