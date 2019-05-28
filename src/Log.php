<?php

namespace Mathrix\OpenAPI\Processor;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;

/**
 * Class Log.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 *
 * @method static bool debug($message, array $context = [])
 * @method static bool info($message, array $context = [])
 * @method static bool notice($message, array $context = [])
 * @method static bool warn($message, array $context = [])
 * @method static bool error($message, array $context = [])
 * @method static bool critical($message, array $context = [])
 * @method static bool alert($message, array $context = [])
 * @method static bool emergency($message, array $context = [])
 */
class Log
{
    /** @var Logger $instance The logger instance. */
    private static $instance;


    /**
     * Make the Logger instance.
     *
     * @param int $level
     */
    private static function make(int $level = Logger::DEBUG): void
    {
        self::$instance = new Logger("default");
        self::$instance->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $level));
    }


    /**
     * Set the Logger level.
     *
     * @param int $level
     */
    public static function setLevel(int $level)
    {
        self::make($level);
    }


    /**
     * Forward static calls to the Logger instance.
     *
     * @param string $name The method name.
     * @param array $args The method arguments.
     */
    public static function __callStatic(string $name, array $args)
    {
        if (self::$instance === null) {
            self::make();
        }

        call_user_func_array([self::$instance, $name], $args);
    }
}
