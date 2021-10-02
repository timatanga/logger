<?php

namespace Tests;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use PHPUnit\Framework\TestCase;
use timatanga\Logger\HandlerManager;
use timatanga\Logger\Logger;

class LoggerTest extends TestCase
{

    public function test_logger_create_file()
    {
        $date = new \DateTime();
        $date = $date->format('Y-m-d');
        $logfile = dirname(__DIR__, 3).'/storage/logs/log-'.$date;

        $logger = new Logger(['main']);
        $logger->info("just an info");

        $content = file_get_contents($logfile);
        $this->assertTrue( strpos($content, 'just an info') > 0 );
    }


    public function test_logger_with_custom()
    {
        $date = new \DateTime();
        $date = $date->format('Y-m-d');
        $logfile = dirname(__DIR__, 3).'/storage/logs/log-'.$date;

        $logger = new Logger('extension', ['extension' => ['handler' => RotatingFileHandler::class]]);
        $logger->info("just an info");
dump($logger);
        $content = file_get_contents($logfile);
        $this->assertTrue( strpos($content, 'just an info') > 0 );
    }

    public function test_logger_combine_context()
    {
        $date = new \DateTime();
        $date = $date->format('Y-m-d');
        $logfile = dirname(__DIR__, 3).'/storage/logs/log-'.$date;

        if ( file_exists($logfile) )
            unlink($logfile);

        $logger = new Logger('main');
        $logger->info("just an info {key}", ['key' => 'message']);

        $content = file_get_contents($logfile);
        $this->assertTrue( strpos($content, 'just an info message') > 0 );
    }


    public function test_logger_with_json_formatter()
    {
        $logfile = dirname(__DIR__, 3).'/storage/logs/error.log';

        if ( file_exists($logfile) )
            unlink($logfile);

        $logger = new Logger(['error']);
        $logger->error(['key1' => 'value1', 'key2' => 'value2']);

        $content = file_get_contents($logfile);
        $this->assertTrue( strpos($content, "key2") > 0 );
    }


    public function test_logger_with_array()
    {
        $logfile = dirname(__DIR__, 3).'/storage/logs/error.log';

        if ( file_exists($logfile) )
            unlink($logfile);

        $logger = new Logger(['error']);
        $logger->error(['key' => ['value 1', 'value 2']]);

        $content = file_get_contents($logfile);
        $this->assertTrue( strpos($content, "value 2") > 0 );
    }

}
