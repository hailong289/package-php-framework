<?php

namespace System\Core;

class Curl
{
    private $curlOptions = [
        'RETURNTRANSFER' => true,
        'FAILONERROR' => false,
        'FOLLOWLOCATION' => false,
        'CONNECTTIMEOUT' => 30,
        'TIMEOUT' => 30,
        'USERAGENT' => '',
        'URL' => '',
        'POST' => false,
        'HTTPHEADER' => [],
        'SSL_VERIFYPEER' => false,
        'NOBODY' => false,
        'HEADER' => false,
    ];
    protected $packageOptions = [
        'data' => [],
        'files' => [],
        'requestJson' => false,
        'responseJson' => false,
        'returnAsArray' => false,
        'responseObject' => false,
        'responseArray' => false,
        'enableDebug' => false,
        'xDebugSessionName' => '',
        'containsFile' => false,
        'debugFile' => '',
        'saveFile' => '',
    ];

    public static function instance(){
        return new Curl();
    }
    /**
     * Add a HTTP header to the request
     * @param   string $header      The HTTP header that is to be added to the request
     */
    public function header($header)
    {
        $this->curlOptions[ 'HTTPHEADER' ][] = $header;
        return $this;
    }
    /**
     * Add multiple HTTP header at the same time to the request
     * @param   array $headers      Array of HTTP headers that must be added to the request
     */
    public function headers($headers)
    {
        $data = array();
        foreach( $headers as $key => $value ) {
            if( !is_numeric($key) ) {
                $value = $key .': '. $value;
            }

            $data[] = $value;
        }

        $this->curlOptions[ 'HTTPHEADER' ] = array_merge(
            $this->curlOptions[ 'HTTPHEADER' ], $data
        );
        return $this;
    }
    /**
     * Add an HTTP Authorization header to the request
     *
     * @param   string $token       The authorization token that is to be added to the request
     */
    public function authorization($token)
    {
        return $this->header( 'Authorization: ' . $token );
    }
    /**
     * Add a HTTP bearer authorization header to the request
     * @param   string $bearer      The bearer token that is to be added to the request
     */
    public function bearer($bearer)
    {
        return $this->authorization(  'Bearer '. $bearer );
    }
    /**
     * Add a content type HTTP header to the request
     * @param   string $contentType    The content type of the file you would like to download
     */
    public function contentType($contentType)
    {
        return $this->header( 'Content-Type: '. $contentType )->header( 'Connection: Keep-Alive' );
    }
    /**
     * Add GET or POST data to the request
     * @param   mixed $data     Array of data that is to be sent along with the request
     */
    public function withData($data = array())
    {
        return $this->withPackageOption('data', $data );
    }
    /**
     * Add a file to the request
     * @param   string $key          Identifier of the file (how it will be referenced by the server in the $_FILES array)
     * @param   string $path         Full path to the file you want to send
     * @param   string $mimeType     Mime type of the file
     * @param   string $postFileName Name of the file when sent. Defaults to file name
     */
    public function withFile($key, $path, $mimeType = '', $postFileName = '')
    {
        $fileData = array(
            'fileName'     => $path,
            'mimeType'     => $mimeType,
            'postFileName' => $postFileName,
        );
        $this->packageOptions['files'][$key] = $fileData;
        return $this->containsFile();
    }
    /**
     * Allow for redirects in the request
     */
    public function allowRedirect()
    {
        return $this->withCurlOption( 'FOLLOWLOCATION', true );
    }
    /**
     * Configure the package to encode and decode the request data
     * @param   boolean $asArray    Indicates whether or not the data should be returned as an array. Default: false
     */
    public function asJson($asArray = false)
    {
        return $this->requestJson()->responseJson($asArray);
    }
    /**
     * Configure the package to encode the request data to json before sending it to the server
     */
    public function requestJson()
    {
        return $this->withPackageOption('requestJson', true);
    }
    /**
     * Configure the package to decode the request data from json to object or associative array
     * @param   boolean $asArray    Indicates whether or not the data should be returned as an array. Default: false
     */
    public function responseJson($asArray = false)
    {
        return $this->withPackageOption('responseJson', true )
            ->withPackageOption('returnAsArray', $asArray );
    }
    /**
     * Set the request timeout
     * @param   float $timeout    The timeout for the request (in seconds, fractions of a second are okay. Default: 30 seconds)
     */
    public function timeout($timeout = 30.0) {
        return $this->withCurlOption('TIMEOUT_MS', ($timeout * 1000));
    }
    /**
     * Set the connect timeout
     * @param   float $timeout    The connect timeout for the request (in seconds, fractions of a second are okay. Default: 30 seconds)
     */
    public function connectTimeout($timeout = 30.0)
    {
        return $this->withCurlOption('CONNECTTIMEOUT_MS', ($timeout * 1000));
    }
    /**
     * Set any specific cURL option
     *
     * @param   string $key         The name of the cURL option
     * @param   string $value       The value to which the option is to be set
     */
    protected function withCurlOption($key, $value)
    {
        $this->curlOptions[ $key ] = $value;
        return $this;
    }
    /**
     * Set any specific package option
     *
     * @param   string $key       The name of the cURL option
     * @param   string $value     The value to which the option is to be set
     */
    protected function withPackageOption($key, $value)
    {
        $this->packageOptions[ $key ] = $value;
        return $this;
    }
    /**
     * Add response headers to the response object or response array
     */
    public function responseHeaders()
    {
        return $this->withCurlOption( 'HEADER', TRUE );
    }
    /**
     * Return a full response object with HTTP status and headers instead of only the content
     */
    public function responseObject()
    {
        return $this->withPackageOption( 'responseObject', true );
    }
    /**
     * Return a full response array with HTTP status and headers instead of only the content
     */
    public function responseArray()
    {
        return $this->withPackageOption( 'responseArray', true );
    }
    /**
     * Enable File sending
     */
    public function containsFile()
    {
        return $this->withPackageOption( 'containsFile', true );
    }

    /**
     * Enable Proxy for the cURL request
     *
     * @param string $proxy Hostname
     * @param string $port Port to be used
     * @param string $type Scheme to be used by the proxy
     * @param string $username Authentication username
     * @param string $password Authentication password
     */
    public function withProxy($proxy, $port = '', $type = '', $username = '', $password = '')
    {
        $this->withCurlOption( 'PROXY', $proxy );
        if( !empty($port) ) {
            $this->withCurlOption( 'PROXYPORT', $port );
        }
        if( !empty($type) ) {
            $this->withCurlOption( 'PROXYTYPE', $type );
        }
        if( !empty($username) && !empty($password) ) {
            $this->withCurlOption( 'PROXYUSERPWD', $username .':'. $password );
        }
        return $this;
    }
    /**
     * Enable debug mode for the cURL request
     *
     * @param   string $logFile    The full path to the log file you want to use
     */
    public function enableDebug($logFile)
    {
        return $this->withPackageOption( 'enableDebug', true )
            ->withPackageOption( 'debugFile', $logFile )
            ->withCurlOption( 'VERBOSE', true );
    }
    /**
     * Add the XDebug session name to the request to allow for easy debugging
     * @param  string $sessionName
     */
    public function enableXDebug($sessionName = 'session_1')
    {
        $this->packageOptions[ 'xDebugSessionName' ] = $sessionName;
        return $this;
    }
    /**
     * Send a GET request to a URL using the specified cURL options
     */
    public function get($url, $data = [])
    {
        if (!empty($data)) {
            $this->packageOptions['data'] = $data;
        }
        $this->withCurlOption('URL',$url);
        $this->appendDataToURL();
        return $this->withCurlOption('CUSTOMREQUEST', 'GET')->send();
    }
    /**
     * Send a POST request to a URL using the specified cURL options
     */
    public function post($url, $data = [])
    {
        $this->withCurlOption('URL',$url);
        $this->setPostParameters($data);
        return $this->send();
    }

    /**
     * Send a PUT request to a URL using the specified cURL options
     */
    public function put($url, $data = [])
    {
        $this->withCurlOption('URL',$url);
        $this->setPostParameters($data);
        return $this->withCurlOption('CUSTOMREQUEST', 'PUT')->send();
    }

    /**
     * Send a PATCH request to a URL using the specified cURL options
     */
    public function patch($url, $data = [])
    {
        $this->withCurlOption('URL',$url);
        $this->setPostParameters($data);
        return $this->withCurlOption('CUSTOMREQUEST', 'PATCH')->send();
    }

    /**
     * Send a DELETE request to a URL using the specified cURL options
     */
    public function delete($url, $data = [])
    {
        $this->withCurlOption('URL',$url);
        $this->setPostParameters($data);
        return $this->withCurlOption('CUSTOMREQUEST', 'DELETE')->send();
    }
    /**
     * Send a download request to a URL using the specified cURL options
     * @param  string $fileName
     */
    public function download($url, $fileName)
    {
        $this->withCurlOption('URL',$url);
        $this->appendDataToURL();
        $this->packageOptions['saveFile'] = $fileName;
        return $this->send();
    }
    /**
     * Send a HEAD request to a URL using the specified cURL options
     */
    public function head()
    {
        $this->withCurlOption('URL',$url);
        $this->appendDataToURL();
        $this->withCurlOption('NOBODY', true);
        $this->withCurlOption('HEADER', true);
        return $this->send();
    }

    private function setPostParameters($data)
    {
        if(!empty($data)) {
            $this->packageOptions['data'] = $data;
        }
        $this->curlOptions[ 'POST' ] = true;
        $parameters = $this->packageOptions['data'];
        if(!empty($this->packageOptions['files'])) {
            foreach( $this->packageOptions['files'] as $key => $file ) {
                $parameters[$key] = $this->getCurlFileValue( $file[ 'fileName' ], $file[ 'mimeType' ], $file[ 'postFileName'] );
            }
        }

        if($this->packageOptions['requestJson']) {
            $parameters = \json_encode($parameters);
        }

        $this->curlOptions[ 'POSTFIELDS' ] = $parameters;
    }

    private function getCurlFileValue($filename, $mimeType, $postFileName)
    {
        // PHP 5 >= 5.5.0, PHP 7
        if(function_exists('curl_file_create')) {
            return curl_file_create($filename, $mimeType, $postFileName);
        }

        // Use the old style if using an older version of PHP
        $value = "@{$filename};filename=" . $postFileName;
        if( $mimeType ) {
            $value .= ';type=' . $mimeType;
        }

        return $value;
    }


    private function forgeOptions()
    {
        $results = [];
        foreach($this->curlOptions as $key => $value) {
            $arrayKey = constant('CURLOPT_' . $key);

            if(!$this->packageOptions['containsFile'] && $key === 'POSTFIELDS' && is_array($value) ) {
                $results[$arrayKey] = http_build_query($value);
            } else {
                $results[$arrayKey] = $value;
            }
        }

        if(!empty($this->packageOptions['xDebugSessionName'])) {
            $char = strpos($this->curlOptions[ 'URL' ], '?') ? '&' : '?';
            $this->curlOptions['URL'] .= $char . 'XDEBUG_SESSION_START='. $this->packageOptions[ 'xDebugSessionName' ];
        }

        return $results;
    }

    private function send()
    {
        if($this->packageOptions['requestJson']) {
            $this->header( 'Content-Type: application/json' );
        }

        if($this->packageOptions['enableDebug']) {
            $debugFile = fopen($this->packageOptions['debugFile'], 'w');
            $this->withCurlOption('STDERR', $debugFile);
        }
        $curl = curl_init();
        $options = $this->forgeOptions();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $responseHeader = null;
        if($this->curlOptions['HEADER']) {
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE );
            $responseHeader = substr( $response, 0, $headerSize );
            $response = substr( $response, $headerSize );
        }

        // Capture additional request information if needed
        $responseData = array();
        if($this->packageOptions['responseObject'] || $this->packageOptions['responseArray']) {
            $responseData = curl_getinfo($curl);
            if(curl_errno($curl)) {
                $responseData['errorMessage'] = curl_error($curl);
            }
        }

        curl_close($curl);

        if($this->packageOptions['saveFile']) {
            // Save to file if a filename was specified
            $file = fopen($this->packageOptions['saveFile'], 'w');
            fwrite($file, $response);
            fclose($file);
        } else if($this->packageOptions['responseJson']) {
            // Decode the request if necessary
            $response = json_decode($response, $this->packageOptions[ 'returnAsArray' ]);
        }

        if( $this->packageOptions['enableDebug'] ) {
            fclose($debugFile);
        }

        return $this->response($response, $responseData, $responseHeader);
    }

    private function response($content,$responseData = [], $header = null)
    {
        if(!$this->packageOptions['responseObject'] && !$this->packageOptions['responseArray']) {
            return $content;
        }

        $object = new \stdClass();
        $object->content = $content;
        $object->status = $responseData[ 'http_code' ];
        $object->contentType = $responseData[ 'content_type' ];
        if(array_key_exists('errorMessage', $responseData) ) {
            $object->error = $responseData['errorMessage'];
        }

        if($this->curlOptions['HEADER']) {
            $object->headers = $this->parseHeaders($header);
        }

        if($this->packageOptions['responseObject'] ) {
            return $object;
        }

        if($this->packageOptions['responseArray']) {
            return (array)$object;
        }

        return $content;
    }

    private function parseHeaders($headerString)
    {
        $headers = array_filter(array_map(function ($x) {
            $arr = array_map('trim', explode(':', $x, 2));
            if(count($arr) == 2) {
                return [$arr[0] => $arr[1]];
            }
        }, array_filter(array_map('trim', explode("\r\n", $headerString)))));

        $results = array();

        foreach($headers as $values) {
            if(!is_array($values)) {
                continue;
            }

            $key = array_keys($values)[0];
            if(isset($results[$key])) {
                $results[$key] = array_merge(
                    (array)$results[$key],
                    array(array_values($values)[0])
                );
            } else {
                $results = array_merge(
                    $results,
                    $values
                );
            }
        }
        return $results;
    }

    private function appendDataToURL()
    {
        $parameterString = '';
        if(is_array($this->packageOptions['data']) && count($this->packageOptions['data']) != 0) {
            $parameterString = '?'. http_build_query($this->packageOptions['data']);
        }

        return $this->curlOptions['URL'] .= $parameterString;
    }
}