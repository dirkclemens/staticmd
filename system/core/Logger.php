<?php
namespace StaticMD\Core;
class Logger {
    private static $logFile = null;

    private static function getLogFile() {
        if (self::$logFile === null) {
            // Projekt-Root relativ zu /system/core/
            self::$logFile = dirname(__DIR__, 2) . '/debug.log';
        }
        return self::$logFile;
    }

    public static function debug($message) {
        self::writeLog('DEBUG', $message);
    }

    public static function info($message) {
        self::writeLog('INFO', $message);
    }

    public static function error($message) {
        self::writeLog('ERROR', $message);
    }

    private static function writeLog($level, $message) {
        $date = date('Y-m-d H:i:s');
        $entry = "[$date] [$level] $message\n";
        file_put_contents(self::getLogFile(), $entry, FILE_APPEND);
    }
}
