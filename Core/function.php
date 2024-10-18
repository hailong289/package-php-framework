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
        $string = rtrim(strtolower($string), $delimiter);
        $string = ltrim($string, $delimiter);
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
        $view = preg_replace('/([.]+)/', '/' , $view);
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

if(!function_exists('logs')){
    /**
     * @return InterfaceLogs|__anonymous@2793
     */
    function logs(): object {
        return new class implements \Hola\Interfaces\InterfaceLogs\Log {
            public function dump(...$args) {
                http_response_code(500);
                echo "<pre>";
                print_r($args);
                echo "</pre>";
                exit();
            }
            public function dump_html(...$args) {
                http_response_code(500);
                echo "<pre>";
                print_r($args, true);
                echo "</pre>";
                exit();
            }
            function write($data, $name_file = 'debug') {
                $date = "\n\n[".date('Y-m-d H:i:s')."]: ";
                $data = json_encode($data);
                if (!file_exists(__DIR__ROOT .'/storage')) {
                    mkdir(__DIR__ROOT .'/storage', 0777, true);
                }
                file_put_contents(__DIR__ROOT ."/storage/$name_file.log",$date . $data . PHP_EOL, FILE_APPEND);
                return $this;
            }
            function debug($data) {
                $date = "\n\n[".date('Y-m-d H:i:s')."]: ";
                $data = json_encode($data);
                file_put_contents(__DIR__ROOT .'/storage/debug.log',$date . $data . PHP_EOL, FILE_APPEND);
                return $this;
            }
        };
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
        $view = preg_replace('/([.]+)/', '/' , $name);
        if(!file_exists(__DIR__ROOT . '/App/Views/'.$view.'.view.php')){
            throw new \RuntimeException("File App/Views/$view.view.php does not exist", 500);
        }
        extract($data);
        $file = __DIR__ROOT . '/App/Views/'.$view.'.view.php';
        require_once $file;
        return $file;
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
    /**
     * @param $key
     * @return object|InterfaceErrors|mixed|__anonymous@7689
     */
    function errors($key = ''): object {
        if(!empty($key)) {
            return $GLOBALS['share_data_errors'][$key];
        }
        return new class() implements \Hola\Interfaces\FunctionInterface\InterfaceErrors {
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
    function val($key = '', $default = null) {
        return $GLOBALS['share_data_view'][$key] ?? $default;
    }
}

if(!function_exists('res')){
    /**
     * @return InterfaceRes|__anonymous@8576
     */
    function res() {
        return new class() implements \Hola\Interfaces\FunctionInterface\InterfaceRes {
            function view($name, $data = [], $status = 200) {
                $view = preg_replace('/([.]+)/', '/' , $name);
                if(!file_exists(__DIR__ROOT . '/App/Views/'.$view.'.view.php')){
                    throw new \RuntimeException("File App/Views/$view.view.php does not exist", 500);
                }
                http_response_code($status);
                extract($data);
                $file = __DIR__ROOT . '/App/Views/'.$view.'.view.php';
                require_once $file;
                return $file;
            }
            function data($data = []) {
                return $this;
            }
            function json($data, $status = 200){
                http_response_code($status);
                header('Accept: application/json');
                return $data;
            }
        };
    }
}


if(!function_exists('collection')) {
    function collection($data = [])
    {
        return new \Hola\Data\Collection($data);
    }
}

if (!function_exists('sendJobs')) {
    /**
     * @param $job
     * @param $queue_name
     * @param $connection
     * @param $timeout
     * @return void
     * @throws Throwable
     */
    function sendJobs($job, $queue_name = null, $connection = null, $timeout = null) {
        $queue = \Hola\Queue\CreateQueue::instance();
        if (!is_null($queue_name)) {
            $queue->setQueue($queue_name);
        }
        if (!is_null($connection)) {
            $queue->connection($connection);
        }
        if (!is_null($timeout)) {
            $queue->setTimeOut($timeout);
        }
        $queue->enQueue($job);
    }
}

if(!function_exists('cache')) {
    function cache($name = null, $data = []) {
        if (!file_exists(__DIR__ROOT .'/storage/cache')) {
            mkdir(__DIR__ROOT .'/storage/cache', 0777, true);
        }

        if (is_null($name)) {
            return new class implements \Hola\Interfaces\FunctionInterface\InterfaceCacheFile {
                public function set($name, $data = []){
                    $folder = explode('/', $name);
                    if (count($folder) > 1) {
                        unset($folder[count($folder) - 1]);
                        $folder = implode('/', $folder);
                        $path_cache = __DIR__ROOT .'/storage/cache/'.$folder;
                        if (!file_exists($path_cache)) {
                            if (!mkdir($path_cache, 0777, true) && !is_dir($path_cache)) {
                                throw new \Exception(sprintf('Directory "%s" was not created', $path_cache));
                            }
                        }
                    }
                    file_put_contents(__DIR__ROOT ."/storage/cache/$name.cache", serialize($data));
                    return $this;
                }
                public function get($name){
                    $data_cache = file_get_contents(__DIR__ROOT ."/storage/cache/$name.cache");
                    return unserialize($data_cache ?? '');
                }
                public function clear($name){
                    $file = __DIR__ROOT ."/storage/cache/$name.cache";
                    if(file_exists($file)){
                        unlink($file);
                    }
                    return $this;
                }
            };
        }

        $data_cache = file_get_contents(__DIR__ROOT ."/storage/cache/$name.cache");
        if (empty($data_cache)) {
            file_put_contents(__DIR__ROOT ."/storage/cache/$name.cache", serialize($data));
            return $data;
        } else {
            return unserialize($data_cache);
        }
    }
}

if(!function_exists('rglob')) {
    function rglob($pattern, $flags = 0) {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge(
                [],
                ...[$files, rglob($dir . "/" . basename($pattern), $flags)]
            );
        }
        return $files;
    }
}

if (!function_exists('app')) {
    function app($abstract = null) {
        if (is_null($abstract)) {
            return \Hola\Container\Container::instance();
        }
        return \Hola\Container\Container::instance()->make($abstract);
    }
}

if (!function_exists('config')) {
    function config($name = null) {
        if (is_null($name)) {
            return new class implements \Hola\Interfaces\FunctionInterface\InterfaceConfig {
                function set($name, $value) {
                    $arr_config = explode('.', $name);
                    $name_config = $arr_config[0];
                    unset($arr_config[0]);
                    $config = $GLOBALS['config'][$name_config] ?? [];
                    $this->editByKey($config, $arr_config, $value);
                    $GLOBALS['config'][$name_config] = $config;
                }
                function get($name) {
                    $arr_config = explode('.', $name);
                    $name_config = $arr_config[0];
                    unset($arr_config[0]);
                    $config = $GLOBALS['config'][$name_config] ?? [];
                    return $this->last($arr_config, $config);
                }
                function last($keys, $items) {
                    foreach ($keys as $key) {
                        if (isset($items[$key])) {
                            $items = $items[$key];
                        } else {
                            return null; // Key does not exist
                        }
                    }
                    return $items;
                }
                function editByKey(&$array, $keys, $value) {
                    if (is_array($array) && !empty($keys)) {
                        $key = array_shift($keys);
                        if (empty($keys)) {
                            if (isset($array[$key])) {
                                $array[$key] = $value;
                            }
                        } else {
                            if (isset($array[$key]) && is_array($array[$key])) {
                                $this->editByKey($array[$key], $keys, $value);
                            }
                        }
                    }
                }
            };
        }
        $arr_config = explode('.', $name);
        $name_config = $arr_config[0];
        unset($arr_config[0]);
        $config = $GLOBALS['config'][$name_config] ?? [];
        return config()->last($arr_config, $config);
    }
}

if (!function_exists('createFolder')) {
    function createFolder($path, $mode = 0777) {
        if (!empty($path) && !file_exists($path)) {
            if (!mkdir($concurrentDirectory = $path, $mode, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
    }
}

if (!function_exists('getFolder')) {
    function getFolder($link) {
        preg_match_all('/\b[^\/]+\/\b/', $link, $matches);
        return implode('', $matches[0]);
    }
}

if(!function_exists('isTwoDimensionalArray')) {
    function isTwoDimensionalArray($array) {
        if (!is_array($array)) {
            return false;
        }
        foreach ($array as $element) {
            if (is_array($element)) {
                return true;
            }
        }
        return false;
    }
}