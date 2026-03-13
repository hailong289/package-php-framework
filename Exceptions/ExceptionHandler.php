<?php

namespace Hola\Exceptions;

use Hola\Transport\Request;
use Hola\Transport\Response;
use Hola\Transport\ResponseBuilder;
use Throwable;

class ExceptionHandler
{
    public static function handle(Throwable $e)
    {
        $request = new Request();
        self::writeLogs($e, $request);
        $app_debug = conval('APP_DEBUG', false);
        if (!$app_debug) {
            $code = self::getStatusCode($e->getCode());
            $errors = [
                "message" => $code === 500 ? "Internal Server Error" : $e->getMessage(),
                "code" => $code
            ];
        } else {
            $errors = [
                "message" => $e->getMessage(),
                "code" => self::getStatusCode($e->getCode()),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
                "trace" => $e->getTraceAsString(),
                "previous" => $e->getPrevious()
            ];
        }
        if ($request->isJson()) {
            $res = Response::json($errors)->setStatus($errors['code']);
            return self::responseCore($res);
        }
        $res = Response::view('error.index', $errors)->setStatus($errors['code']);
        return self::responseCore($res);
    }

    private static function getStatusCode($code) {
        $code = (int)$code;
        return $code ? $code : 500;
    }
    
    private static function responseCore($response) {
        if ($response instanceof ResponseBuilder) {
            return $response->send();
        }

        if (is_array($response) || is_object($response)) {
            return Response::json($response)->send();
        }

        if (is_string($response)) {
            if (is_file($response)) {
                return Response::file($response)->send();
            }

            return Response::text($response)->send();
        }

        if ($response instanceof \Closure) {
            return $response();
        }

        return Response::text('Invalid response')->send();
    }
    
    private static function writeLogs(Throwable $e, $request)
    {
        $storagePath = __DIR__ROOT . '/storage';
        if (!file_exists($storagePath) && !mkdir($storagePath, 0777, true) && !is_dir($storagePath)) {
            echo sprintf('Directory "%s" was not created', $storagePath);
            return;
        }


        $storagePath = __DIR__ROOT . '/storage';
        $logFile = "$storagePath/application.log";

        $errorMessage = sprintf(
            "[%s][%d]: %s in %s on line %d\n%s\n\n",
            date('Y-m-d H:i:s'),
            self::getStatusCode($e->getCode()),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        file_put_contents($logFile, $errorMessage, FILE_APPEND);

        app()->event()->trigger('app.exceptions', [
            'type' => 'event_exceptions',
            'message' => $e->getMessage(),
            'code' => self::getStatusCode($e->getCode()),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTraceAsString(),
            'class' => get_class($e),
            'previous' => $e->getPrevious()
        ]);

        $currentException = $e;
        $level = 0;
        while ($currentException->getPrevious()) {
            $level++;
            $currentException = $currentException->getPrevious();
            $logMessage = sprintf(
                "[%s] Error level %d: %s in %s on line %d\n",
                date('Y-m-d H:i:s'),
                $level,
                $currentException->getMessage(),
                $currentException->getFile(),
                $currentException->getLine()
            );
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        }
    }
}