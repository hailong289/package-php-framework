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
        $search = array(
            '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
            '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
            '#(ì|í|ị|ỉ|ĩ)#',
            '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
            '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
            '#(ỳ|ý|ỵ|ỷ|ỹ)#',
            '#(đ)#',
            '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
            '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
            '#(Ì|Í|Ị|Ỉ|Ĩ)#',
            '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
            '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
            '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
            '#(Đ)#',
            "/[^a-zA-Z0-9\-\_]/",
        );
        $replace = array(
            'a',
            'e',
            'i',
            'o',
            'u',
            'y',
            'd',
            'A',
            'E',
            'I',
            'O',
            'U',
            'Y',
            'D',
            '-',
        );
        $string = preg_replace($search, $replace, $str);
        $string = preg_replace('/(-)+/', $delimiter, $string);
        $string = strtolower($string);
        return $string;
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
        if(!file_exists(__DIR__ROOT . '/App/Views/'.$view.'.view.php')){
            throw new \RuntimeException("File App/Views/$view.view.php does not exist", 500);
        }
        return __DIR__ROOT . '/App/Views/'.$view.'.view.php';
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
    function log_write($e, $name = 'debug') {
        $date = "\n\n[".date('Y-m-d H:i:s')."]: ";
        if (!file_exists(__DIR__ROOT .'/storage')) {
            mkdir(__DIR__ROOT .'/storage', 0777, true);
        }
        file_put_contents(__DIR__ROOT ."/storage/$name.log",$date . $e, FILE_APPEND);
    }
}

if(!function_exists('get_view')){
    function get_view($name, $data = [])
    {
        if(!file_exists(__DIR__ROOT . '/App/Views/'.$name.'.view.php')){
            throw new \RuntimeException("File App/Views/$name.view.php does not exist", 500);
        }
        if(isset($GLOBALS['share_date_view']) && count($GLOBALS['share_date_view'])) $data = array_merge($data, $GLOBALS['share_date_view']);
        extract($data);
        $view = preg_replace('/([.]+)/', '/' , $name);
        require_once __DIR__ROOT . '/App/Views/'.$name.'.view.php';
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
        return defined($value) && constant($value) ?  constant($value):$default;
    }
}

if(!function_exists('uid')){
    function uid($data = null) {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if(!function_exists('errors')){
    function errors($key = ''): object {
        if(!empty($key)) {
            return $GLOBALS['share_data_errors'][$key];
        }
        return new class() {
            function get($key = '') {
                return $GLOBALS['share_data_errors'][$key];
            }
            function set($key = '', $value = '') {
                $GLOBALS['share_data_errors'][$key] = $value;
                return;
            }
            function all(){
                return $GLOBALS['share_data_errors'];
            }
        };
    }
}

if(!function_exists('val')){
    function val($key = '') {
        return $GLOBALS['date_view'][$key];
    }
}

if(!function_exists('res')){
    function res() {
        return new class() {
            function view($name, $data = [], $status = 200) {
                if(!file_exists(__DIR__ROOT . '/App/Views/'.$name.'.view.php')){
                    throw new \RuntimeException("File App/Views/$name.view.php does not exist", 500);
                }
                http_response_code($status);
                if(count($data)) $GLOBALS['share_date_view'] = $data;
                extract($data);
                $GLOBALS['date_view'] = $data;
                $view = preg_replace('/([.]+)/', '/' , $name);
                require_once __DIR__ROOT . '/App/Views/'.$name.'.view.php';
            }
            function data($data = []) {
                if(count($GLOBALS['share_date_view'])) {
                    $GLOBALS['share_date_view'] = array_merge($data, $GLOBALS['share_date_view']);
                } else {
                    $GLOBALS['share_date_view'] = $data;
                }
                return $this;
            }
            function json($data, $status = 200){
                http_response_code($status);
                return $data;
            }
        };
    }
}
