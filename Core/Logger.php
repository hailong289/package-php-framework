<?php
namespace Hola\Core;
use Throwable;

class Logger
{
    private static $folder = __DIR__ROOT . "/storage";
    private static string $file = "application.log";
    
    private static function checkPermissions(): void
    {
        if (!file_exists(self::$folder) && !mkdir(self::$folder, 0777, true) && !is_dir(self::$folder)) {
            echo sprintf('Directory "%s" was not created', self::$folder);
            return;
        }
    }

    public static function error(Throwable $e): void
    {
        self::checkPermissions();
        
        $logFile = self::$folder . "/" . self::$file;

        $errorMessage = sprintf(
            "[ERROR][%s][%d]: %s in %s on line %d\n%s\n\n",
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

    public static function info($text, $data = []): void {
        self::checkPermissions();

        $logFile = self::$folder . "/" . self::$file;

        $infoMessage = sprintf(
            "[INFO][%s][%d]: %s %s",
            date('Y-m-d H:i:s'),
            self::getStatusCode($e->getCode()),
            $text,
            json_encode($data, JSON_UNESCAPED_SLASHES)
        );

        file_put_contents($logFile, $infoMessage, FILE_APPEND);
    }

    public static function warning($text, $data = []): void {
        self::checkPermissions();

        $logFile = self::$folder . "/" . self::$file;

        $warningMessage = sprintf(
            "[WARNING][%s][%d]: %s %s",
            date('Y-m-d H:i:s'),
            self::getStatusCode($e->getCode()),
            $text,
            json_encode($data, JSON_UNESCAPED_SLASHES)
        );

        file_put_contents($logFile, $warningMessage, FILE_APPEND);
    }

    public function debug($text, $data = []): void
    {
        self::checkPermissions();

        $logFile = self::$folder . "/" . self::$file;

        $debugMessage = sprintf(
            "[DEBUG][%s][%d]: %s %s",
            date('Y-m-d H:i:s'),
            self::getStatusCode($e->getCode()),
            $text,
            json_encode($data, JSON_UNESCAPED_SLASHES)
        );

        file_put_contents($logFile, $debugMessage, FILE_APPEND);
    }
    
}