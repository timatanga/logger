<?php

/*
 * This file is part of the Logger package.
 *
 * (c) Mark Fluehmann dbiz.apps@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\SyslogUdpHandler;
use timatanga\Logger\Formatter\JsonPrettyFormatter;
use timatanga\Logger\Formatter\LinePrettyFormatter;

return [


    /**
     * Default logfile
     * If not explicitly set in a channel configuration the logfile is named like this
     */
    'logFile' => 'log',

    /**
     * Logfile directory
     * Filehandlers are writing logfiles to this relative location in regard to the root directory
     */
    'logPath' => 'storage/logs',

    /**
     * Logger Timezone
     * Overwrites default timezone which is UTC
     */
    'timezone' => 'Europe/Berlin',

    /**
     * Logging Dateformat
     * Logs are recorded in this datetime format
     */
    'dateFormat' => 'Y-m-d H:i:s',

    /**
     * Channels
     * Selection of preconfigured log channels
     */
    'channels' => [

        'null' => [
            'handler' => NullHandler::class,
            'config' => [
                'level' => 'debug',
            ]
        ],

        'main' => [
            'handler' => RotatingFileHandler::class,
            'formatter' => LinePrettyFormatter::class,
            'config' => [
                'filename' => 'log',
                'maxFiles' => 10,
                'level' => env('LOG_LEVEL_MAIN', 'debug'),
            ]
        ],        

        'syslog' => [
            'handler' => SyslogHandler::class,
            'config' => [
                'level' => env('LOG_LEVEL_SYSLOG', 'debug'),
            ]
        ],  

        'error' => [
            'handler' => StreamHandler::class,
            'formatter' => LinePrettyFormatter::class,
            'config' => [
                'stream' => 'error.log',
                'level' => env('LOG_LEVEL_ERROR', 'error'),
            ]
        ],  

        'slack' => [
            'handler' => SlackWebhookHandler::class,
            'config' => [
                'level' => env('LOG_LEVEL_SLACK', 'critical'),
            ]
        ],
    ],
];


    