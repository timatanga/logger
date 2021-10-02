<?php

/*
 * This file is part of the Logger package.
 *
 * (c) Mark Fluehmann dbiz.apps@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace timatanga\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Monolog;
use Psr\Log\LoggerInterface;
use timatanga\Logger\Exceptions\LoggerConfigException;
use timatanga\Logger\ResolveLogger;

class Logger implements LoggerInterface
{

    /**
     * ResolveLogger Instance
     *
     * @var ResolveLogger
     */
    protected $resolver;

    /**
     * Array of \Psr\Log\LoggerInterface instance per channel
     *
     * @var array
     */
    protected $loggers = [];

    /**
     * Globally defined context for loggers
     *
     * @var array
     */
    protected $context = [];


    /**
     * Create a new class instance.
     *
     * @param array|string|null  $channels
     * @param array  $custom
     * @return void
     */
    public function __construct( $channels = null, array $custom = [] )
    {
        // create resolve logger instance
        $this->resolver = new ResolveLogger($custom);

        // set default timezone
        $timezone = $this->resolver->getConfiguration('timezone');
        date_default_timezone_set($timezone);

        // set loggers for channels
        $this->loggers = $this->resolveLoggers($channels);
    }


    /**
     * Resolve logger configurations
     * 
     * @param array|string  $channels
     * @return array
     */
    private function resolveLoggers( $channels )
    {
        $loggers = [];

        foreach ((array) $channels as $channel) {

            // create a log channel
            $logger = new MonologLogger($channel);

            // create handler instance
            $handler = $this->resolver->setChannel($channel)->createInstance();

            // append handler to logger
            $logger->pushHandler($handler);

            $loggers[$channel] = $logger;
        }

        return $loggers;
    }


    /**
     * Get Logger
     *
     * @return array
     */
    public function getChannels()
    {
        return $this->loggers;
    }


    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency( $message, array $context = [] ): void
    {
        $this->pushLog('emergency', $message, $context);
    }


    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert( $message, array $context = [] ): void
    {
        $this->pushLog('alert', $message, $context);

    }


    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical($message, array $context = [] ): void
    {
        $this->pushLog('critical', $message, $context);
    }


    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error( $message, array $context = [] ): void
    {
        $this->pushLog('error', $message, $context);
    }


    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning( $message, array $context = [] ): void
    {
        $this->pushLog('warning', $message, $context);
    }


    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice( $message, array $context = [] ): void
    {
        $this->pushLog('notice', $message, $context);
    }


    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info( $message, array $context = [] ): void
    {
        $this->pushLog('info', $message, $context);
    }


    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug( $message, array $context = [] ): void
    {
        $this->pushLog('debug', $message, $context);
    }


    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log( $level, $message, array $context = [] ): void
    {
        $this->pushLog($level, $message, $context);
    }


    /**
     * Push message to the log.
     *
     * @param  string  $level
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    protected function pushLog( $level, $message, $context )
    {
        foreach ($this->loggers as $logger) {
            $logger->{$level}(
                $message = $this->formatMessage($message),
                $context = array_filter(array_merge($this->context, $context))
            );

        }
    }


    /**
     * Jsonize the parameters for the logger.
     *
     * @param  mixed  $message
     * @return mixed
     */
    protected function formatMessage($message)
    {
        // if ( is_array($message) )
        //     return var_export($message, true);

        return json_encode($message);
    }
}