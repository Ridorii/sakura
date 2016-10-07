<?php
/**
 * Holds the exception (and error) handler.
 * @package Sakura
 */

namespace Sakura;

use ErrorException;
use stdClass;
use Throwable;

/**
 * Exception handler.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class ExceptionHandler
{
    /**
     * Disables checking if the templating engine is available
     * @var bool
     */
    private static $disableTemplate = false;

    /**
     * Register as the error and exception handler.
     */
    public static function register()
    {
        set_exception_handler([static::class, 'exception']);
        set_error_handler([static::class, 'error']);
    }

    /**
     * The entry point for set_exception_handler.
     * @param Throwable $ex
     */
    public static function exception(Throwable $ex)
    {
        $report = config('dev.report_host');

        if ($report !== null) {
            self::report($ex, $report);
        }

        self::view($ex, $report !== null);
    }

    /**
     * The entry point for set_error_handler.
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     */
    public static function error($severity, $message, $file, $line)
    {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Display an error message.
     * @param Throwable $ex
     * @param bool $reported
     */
    private static function view(Throwable $ex, $reported = false)
    {
        http_response_code(500);

        $debug = config('dev.show_errors');
        $cli = php_sapi_name() === 'cli';

        if ($cli || $debug) {
            if (!$cli) {
                header('Content-Type: text/plain');
            }

            echo $ex;
        } else {
            if (!self::$disableTemplate && Template::available()) {
                echo view('errors/500', compact('reported'));
            } else {
                // Disable templates permanently so we don't get stuck in an infinite loop
                // if an exception is caused by the templating engine.
                self::$disableTemplate = true;
                echo "Something broke so badly that the error page failed to render.";

                if ($reported) {
                    echo " The developers have been notified of the issue.";
                }
            }
        }
    }

    /**
     * Posts the exception as json to a remote server.
     * @param Throwable $ex
     * @param string $destination
     */
    private static function report(Throwable $ex, $destination)
    {
        $send = new stdClass;
        $send->Date = date('c');
        $send->Message = $ex->getMessage();
        $send->Code = $ex->getCode();
        $send->File = $ex->getFile();
        $send->Line = $ex->getLine();
        $send->Trace = $ex->getTraceAsString();

        Net::request($destination, 'POST', json_encode($send));
    }
}
