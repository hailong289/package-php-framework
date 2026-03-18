<?php

namespace Hola\Exceptions;

use Hola\Core\Logger;
use Hola\Transport\Request;
use Hola\Transport\Response;
use Hola\Transport\ResponseBuilder;
use Throwable;

class ExceptionHandler
{
    public static function handle(Throwable $e)
    {

        try {
            Logger::error($e);
        } catch (Throwable $loggerError) {
            // Prevent secondary logger failures from hiding the original exception.
        }

        if (PHP_SAPI === 'cli') {
            self::handleCli($e);
            return;
        }

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
        $isJson = false;
        try {
            $request = new Request();
            $isJson = $request->isJson();
        } catch (Throwable $requestError) {
            $isJson = false;
        }

        if ($isJson) {
            $res = Response::json($errors)->setStatus($errors['code']);
            return self::responseCore($res);
        }
        $res = Response::view('error.index', $errors)->setStatus($errors['code']);
        return self::responseCore($res);
    }

    private static function handleCli(Throwable $e): void
    {

        $code = self::getStatusCode($e->getCode());
        $message = sprintf(
            "[CLI ERROR][%d] %s in %s:%d\n %s\n",
            $code,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        file_put_contents('php://stderr', $message);
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
}