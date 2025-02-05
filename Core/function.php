<?php
if(!function_exists('startsWith')){
    /**
     * @param $key
     * @return bool
     */
    function startsWith( $haystack, $needle ) {
        $length = strlen( $needle );
        return substr( $haystack, 0, $length ) === $needle;
    }
}

if(!function_exists('endsWith')){
    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    function endsWith( $haystack, $needle ) {
        $length = strlen( $needle );
        if( !$length ) {
            return true;
        }
        return substr( $haystack, -$length ) === $needle;
    }
}

if(!function_exists('str_slug')){
    /**
     * @param $str
     * @param string $delimiter
     * @return string
     */
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
    /**
     * @param $url
     * @return string
     */
    function path_root($url)
    {
        return str_replace('\\','/',$_SERVER["DOCUMENT_ROOT"] ."/" . $url);
    }
}

if(!function_exists('url')){
    /**
     * @param $path
     * @return string
     */
    function url($path = '')
    {
        return (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . $path;
    }
}

if(!function_exists('view_root')){
    /**
     * @param $view
     * @return string
     */
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
    /**
     * @param $data
     * @return string
     */
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
    /**
     * @param $e
     * @param string $name
     */
    function log_write($e, $name = 'debug') {
        $date = "\n\n[".date('Y-m-d H:i:s')."]: ";
        if (!file_exists(__DIR__ROOT .'/storage')) {
            mkdir(__DIR__ROOT .'/storage', 0777, true);
        }
        file_put_contents(__DIR__ROOT ."/storage/$name.log",$date . $e, FILE_APPEND);
    }
}

if(!function_exists('get_view')){
    /**
     * @param $name
     * @param array $data
     * @return mixed
     */
    function get_view($name, $data = [])
    {
        $view = preg_replace('/([.]+)/', '/' , $name);
        if(!file_exists(__DIR__ROOT . '/App/Views/'.$view.'.view.php')){
            throw new \RuntimeException("File App/Views/$view.view.php does not exist", 500);
        }
        extract($data);
        $file = __DIR__ROOT . '/App/Views/'.$view.'.view.php';
        require_once $file;
    }
}

if(!function_exists('__')){
    /**
     * @param $key
     * @return bool
     */
    function __($key, $data_key = [], $lang = null)
    {
        $language = $lang ?? conval('LANGUAGE', 'vi');
        $data = require(path_root("language/$language.php"));
        $convert = $data[$key] ?? $key;
        foreach ($data_key as $k=>$value) {
            $convert = str_replace("{{".$k."}}", $value, $convert);
        }
        return $convert;
    }
}

if(!function_exists('translate')){
    /**
     * @param $key
     * @return bool
     */
    function translate($key, $data_key = [], $lang = null)
    {
        $language = $lang ?? conval('LANGUAGE', 'vi');
        $data = config()->get($language) ?? [];
        $convert = $data[$key] ?? $key;
        foreach ($data_key as $k=>$value) {
            $convert = str_replace("{{".$k."}}", $value, $convert);
        }
        return $convert;
    }
}

if(!function_exists('lang_has')){
    /**
     * @param $key
     * @return bool
     */
    function lang_has($key)
    {
        $language = conval('LANGUAGE', 'vi');
        $data = config()->get($language) ?? [];
        return isset($data[$key]);
    }
}

if(!function_exists('isDate')){
    /**
     * @param $value
     * @return bool
     */
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
    /**
     * @param $value
     * @return array
     */
    function convert_to_array($value)
    {
        if (!is_array($value) || !is_object($value)) {
            throw new \Hola\Exceptions\AppException('The convert_to_array function parameter is not array or object');
        }

        if (is_array($value)) {
            return $value;
        }

        return json_decode(json_encode($value), true);
    }
}

if(!function_exists('convert_to_object')){
    /**
     * @param $value
     * @return object
     */
    function convert_to_object($value)
    {
        if (!is_array($value) || !is_object($value)) {
           throw new \Hola\Exceptions\AppException('The convert_to_object function parameter is not array or object');
        }

        if (is_object($value)) {
            return $value;
        }

        return json_decode(json_encode($value));
    }
}

if(!function_exists('config_env')){
    /**
     * @param $value
     * @param string $default
     * @return mixed|string
     */
    function config_env($value, $default = '')
    {
        return defined($value) && constant($value) ?  constant($value):$default;
    }
}

if (!function_exists('conval')) {
    /**
     * @param $value
     * @param string $default
     * @param null $first_val
     * @return mixed|string
     */
    function conval($value, $default = '', $first_val = null)
    {
        if (!is_null($first_val)) {
            return $first_val;
        }

        if (defined($value) && constant($value)) {
            return constant($value);
        }

        return $default;
    }
}

if(!function_exists('uid')){
    /**
     * @param $data
     * @return string
     */
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
     * @return object|mixed|null
     */
    function errors($key = ''): object {
        return \Hola\Data\ShareData::init()->getErrorByKey($key);
    }
}

if(!function_exists('val')){
    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    function val($key = '', $default = null) {
        return \Hola\Data\ShareData::init()->get($key, $default);
    }
}

if(!function_exists('res')){
    /**
     * @return \Hola\Transport\Response
     */
    function res() {
        return \Hola\Transport\Response::build();
    }
}

if(!function_exists('collection')) {
    /**
     * @param $data
     * @return \Hola\Data\Collection
     */
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
    /**
     * @param $name
     * @param array $data
     * @return \Hola\Data\Cache
     */
    function cache() {
        $cache = new \Hola\Data\Cache();
        return $cache;
    }
}

if(!function_exists('rglob')) {
    /**
     * Recursive glob
     * @param $pattern
     * @param int $flags
     * @return array
     */
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
    /**
     * Get app instance or make a class instance.
     * @param class-string<T>|null $abstractThe class name to instantiate or null to get the container instance.
     * @return \Hola\Container\Container|T The container instance or the resolved class instance.
     */
    function app($abstract = null) {
        if (is_null($abstract)) {
            return \Hola\Container\Container::instance();
        }
        return \Hola\Container\Container::instance()->make($abstract);
    }
}


if (!function_exists('config')) {
    /**
     * Get config
     * @param string|null $name
     * @return mixed
     */
    function config($name = null) {
        $config = \Hola\Core\ConfigApp::init();
        if (is_null($name)) {
            return $config;
        }
        return config()->get($name);
    }
}

if (!function_exists('createFolder')) {
    /**
     * Create folder
     * @param string $path
     * @param int $mode
     * @throws \RuntimeException
     */
    function createFolder($path, $mode = 0777) {
        if (!empty($path) && !file_exists($path)) {
            if (!mkdir($concurrentDirectory = $path, $mode, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
    }
}

if (!function_exists('getFolder')) {
    /**
     * Get folder from link
     * @param string $link
     * @return string
     */
    function getFolder($link) {
        return dirname($link);
    }
}

if(!function_exists('isTwoDimensionalArray')) {
    /**
     * Check if the array is two-dimensional
     * @param array $array
     * @return bool
     */
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

if(!function_exists('generateKey')) {
    /**
     * Generate random key
     * @param int $length
     * @return string
     */
    function generateKey($length = 16) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

if (!function_exists('findDataByKeys')) {
    /**
     * Find data by keys
     * @param array $keys
     * @param array $items
     * @return mixed|null
     */
    function findDataByKeys($keys, $items) {
        foreach ($keys as $key) {
            if (isset($items[$key])) {
                $items = $items[$key];
            } else {
                return null; // Key does not exist
            }
        }
        return $items;
    }
}

if (!function_exists('updateDataByKeys')) {
    /**
     * Update data by keys
     * @param array $array
     * @param array $keys
     * @param $value
     */
    function updateDataByKeys(&$array, $keys, $value) {
        if (is_array($array) && !empty($keys)) {
            $key = array_shift($keys);
            if (empty($keys)) {
                if (isset($array[$key])) {
                    $array[$key] = $value;
                }
            } else {
                if (isset($array[$key]) && is_array($array[$key])) {
                    updateDataByKeys($array[$key], $keys, $value);
                }
            }
        }
    }
}

if (!function_exists('isXml')) {
    /**
     * Check if the string is XML
     * @param string $string
     * @return bool
     */
    function isXml($string) {
        libxml_use_internal_errors(true);  
        $xml = simplexml_load_string($string);
        if ($xml === false) {
            return false;
        }
        return true;
    }

}