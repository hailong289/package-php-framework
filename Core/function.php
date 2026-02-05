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

if(!function_exists('dump')){
    /**
     * @param $data
     * @return string
     */
    function dump(...$args) {
        http_response_code(500);
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $caller = $trace[0];
        $file = $caller['file'] ?? 'Unknown';
        $line = $caller['line'] ?? 0;

        echo <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Output</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            background: linear-gradient(135deg, #1e1e1e 0%, #2d2d30 100%);
            margin: 0; 
            padding: 20px; 
            font-family: 'SF Mono', 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 13px;
            color: #d4d4d4;
            line-height: 1.6;
        }
        
        .debug-container { max-width: 1400px; margin: 0 auto; }
        
        .debug-card { 
            background: #252526;
            border: 1px solid #454545;
            border-radius: 8px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.6);
            overflow: hidden;
            margin-bottom: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .debug-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.8);
        }
        
        .debug-header {
            background: linear-gradient(135deg, #2d2d30 0%, #333333 100%);
            padding: 12px 16px;
            border-bottom: 2px solid #007acc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .debug-title { 
            display: flex; 
            align-items: center; 
            gap: 8px;
            flex: 1;
            min-width: 200px;
        }
        
        .debug-file { 
            color: #9cdcfe;
            font-weight: 600;
            font-size: 13px;
            word-break: break-all;
        }
        
        .debug-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .debug-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .badge-line { background: #f44336; color: #fff; }
        
        .debug-caller {
            width: 100%;
            margin-top: 8px;
            padding: 8px 12px;
            background: rgba(0, 122, 204, 0.1);
            border-left: 3px solid #007acc;
            border-radius: 4px;
            color: #4ec9b0;
            font-size: 12px;
        }
        
        .debug-content {
            padding: 16px;
            overflow-x: auto;
            background: #1e1e1e;
        }
        
        .debug-item {
            margin-bottom: 20px;
            padding: 12px;
            background: #252526;
            border-radius: 6px;
            border-left: 3px solid #007acc;
        }
        
        .debug-item:last-child { margin-bottom: 0; }
        
        .debug-type {
            color: #4ec9b0;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .debug-data {
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.6;
            font-size: 13px;
        }
        
        .json-key { color: #9cdcfe; font-weight: 500; }
        .json-string { color: #ce9178; }
        .json-number { color: #b5cea8; }
        .json-boolean { color: #569cd6; font-weight: 600; }
        .json-null { color: #569cd6; font-style: italic; opacity: 0.8; }
        .json-bracket { color: #ffd700; font-weight: bold; }
        
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: #1e1e1e; }
        ::-webkit-scrollbar-thumb { background: #555; border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: #777; }
        
        @media (max-width: 768px) {
            body { padding: 10px; }
            .debug-header { padding: 10px; }
            .debug-content { padding: 12px; }
            .debug-meta { font-size: 10px; }
        }
    </style>
</head>
<body>
    <div class="debug-container">
HTML;
        
        echo '<div class="debug-card">';
        echo '<div class="debug-header">';
        echo '<div class="debug-title">';
        echo '<span class="debug-file">' . htmlspecialchars(basename($file)) . '</span>';
        echo '</div>';
        echo '<div class="debug-meta">';
        echo '<span class="debug-badge badge-line">Line: ' . $line . '</span>';
        echo '</div>';
        

        echo '<div class="debug-caller">Full path: ' . htmlspecialchars($file) . '</div>';
        echo '</div>';
        
        echo '<div class="debug-content">';
        
        foreach ($args as $index => $arg) {
            echo '<div class="debug-item">';
            $type = gettype($arg);
            if (is_object($arg)) {
                $type = get_class($arg);
            }
            echo '<div class="debug-type">' . htmlspecialchars($type) . ' (#' . ($index + 1) . ')</div>';
            
            echo '<div class="debug-data">';
            if (is_scalar($arg) || is_null($arg)) {
                echo '<span class="json-' . gettype($arg) . '">' . htmlspecialchars(var_export($arg, true)) . '</span>';
            } else {
                $json = @json_encode($arg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
                
                if ($json === false || $json === 'null') {
                    echo '<span style="color: #dcdcaa">' . htmlspecialchars(print_r($arg, true)) . '</span>';
                } else {
                    $json = htmlspecialchars($json, ENT_QUOTES, 'UTF-8');
                    $json = preg_replace_callback(
                        '/("(?:[^"\\\\]|\\\\.)*"\s*:|:\s*"(?:[^"\\\\]|\\\\.)*"|:\s*(-?\d+\.?\d*)|:\s*(true|false)|:\s*(null)|[\[\]{}])/i',
                        function($matches) {
                            $match = $matches[0];
                            if (preg_match('/".*?"\s*:/', $match)) {
                                return '<span class="json-key">' . $match . '</span>';
                            } elseif (preg_match('/:\s*".*?"/', $match)) {
                                return preg_replace('/(:\s*)(".*?")/', '$1<span class="json-string">$2</span>', $match);
                            } elseif (preg_match('/:\s*-?\d+\.?\d*/', $match)) {
                                return preg_replace('/(:\s*)(-?\d+\.?\d*)/', '$1<span class="json-number">$2</span>', $match);
                            } elseif (preg_match('/:\s*(true|false)/i', $match)) {
                                return preg_replace('/(:\s*)(true|false)/i', '$1<span class="json-boolean">$2</span>', $match);
                            } elseif (preg_match('/:\s*null/i', $match)) {
                                return preg_replace('/(:\s*)(null)/i', '$1<span class="json-null">$2</span>', $match);
                            } elseif (preg_match('/[\[\]{}]/', $match)) {
                                return '<span class="json-bracket">' . $match . '</span>';
                            }
                            return $match;
                        },
                        $json
                    );
                    
                    echo $json;
                }
            }
            
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
        
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
                log_debug(...$args);
                exit();
            }
            
            public function dump_html(...$args) {
                http_response_code(500);
                echo "<pre>";
                print_r($args, true);
                echo "</pre>";
                exit();
            }

            function write(array $data, $name_file = 'application') {
                $date = "\n\n[".date('Y-m-d H:i:s')."]: ";
                $data = implode("\n", $data);
                if (!file_exists(__DIR__ROOT .'/storage')) {
                    mkdir(__DIR__ROOT .'/storage', 0777, true);
                }
                file_put_contents(__DIR__ROOT ."/storage/$name_file.log",$date . $data . PHP_EOL, FILE_APPEND);
                return $this;
            }

            function debug(array $data) {
                $date = "\n\n[".date('Y-m-d H:i:s')."]: ";
                $data = implode("\n", $data);
                file_put_contents(__DIR__ROOT .'/storage/debug.log',$date . $data . PHP_EOL, FILE_APPEND);
                return $this;
            }

            function write_error(\Throwable $e, $name_file = 'application')
            {
                $storagePath = __DIR__ROOT . '/storage';
                if (!file_exists($storagePath) && !mkdir($storagePath, 0777, true) && !is_dir($storagePath)) {
                    echo sprintf('Directory "%s" was not created', $storagePath);
                    return;
                }

                $storagePath = __DIR__ROOT . '/storage';
                $logFile = "$storagePath/$name_file.log";

                $errorMessage = sprintf(
                    "[%s][%d]: %s in %s on line %d\n%s\n\n",
                    date('Y-m-d H:i:s'),
                    $e->getCode(),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $e->getTraceAsString()
                );
                file_put_contents($logFile, $errorMessage, FILE_APPEND | LOCK_EX);
            }
        };
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
        $data = config()->get("language.$language") ?? [];
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
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        if (is_object($value)) {
            return json_decode(json_encode($value), true);
        }

        return [];
    }
}

if(!function_exists('convert_to_object')){
    /**
     * @param $value
     * @return object
     */
    function convert_to_object($value)
    {
        if (is_object($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        if (is_array($value)) {
            return json_decode(json_encode($value));
        }

        return [];
    }
}

if (!function_exists('conval')) {
    /**
     * @param $value
     * @param string $default
     * @return mixed|string
     */
    function conval($value, $default = '')
    {

        if (isset(config('environment')[$value])) {
            return config('environment')[$value];
        }
        
        if (getenv($value)) {
            return getenv($value);
        }

        if (isset($_ENV[$value])) {
            return $_ENV[$value];
        }

        return $default;
    }
}

if (!function_exists('const_get')) {
    /**
     * @param $value
     * @param string $default
     * @return mixed|string
     */
    function const_get($value, $default = '')
    {
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
    function sendJobs($job, $queue_name = null, $drive = null, $connection = null, $timeout = null) {
        $queue = \Hola\Queue\CreateQueue::instance();
        if (!is_null($queue_name)) {
            $queue->setQueue($queue_name);
        }
        if (!is_null($drive)) {
            $queue->driver($drive);
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
        $cache = new \Hola\Data\Cache\CacheManager();
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

if(!function_exists('isTwoDimensionalObject')) {
    /**
     * Check if the object is two-dimensional
     * @param object $array
     * @return bool
     */
    function isTwoDimensionalObject($data) {
        if (!is_object($data)) return false;

        $arrayData = (array) $data;

        foreach ($arrayData as $value) {
            if (!is_object($value)) {
                return false;
            }
        }

        return true;
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

if (!function_exists('csrfToken')) {
    /**
     * Get CSRF token
     * @return string
     */
    function csrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }
}

if (!function_exists('concat')) {
    /**
     * Concatenate strings
     * @param string $glue
     * @param string ...$strings
     * @return string
     */
    function concat($glue = '', ...$strings) {
        return implode($glue, array_filter($strings, 'strlen'));
    }
}

if (!function_exists('share')) {
    /**
     * Share data
     * @param string $key
     * @param mixed $value
     * @return \Hola\Data\ShareData
     */
    function share()
    {
        return \Hola\Data\ShareData::init();
    }
}