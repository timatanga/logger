<?php

namespace Tests;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use PHPUnit\Framework\TestCase;
use timatanga\Logger\Exceptions\LoggerConfigException;
use timatanga\Logger\HandlerManager;
use timatanga\Logger\Logger;
use timatanga\Logger\ResolveConfig;

class ConfigurationTest extends TestCase
{

    public function test_read_config_file()
    {
        $config = ResolveConfig::resolve();

        $this->assertTrue( isset($config['logFile']) );
        $this->assertTrue( isset($config['logPath']) );
        $this->assertTrue( isset($config['timezone']) );
        $this->assertTrue( isset($config['dateFormat']) );
        $this->assertTrue( is_dir($config['logPath']) );
    }


    public function test_read_config_file_has_null_handler()
    {
        $config = ResolveConfig::resolve();

        $this->assertTrue( $config['channels']['null']['handler'] == 'Monolog\Handler\NullHandler' );
    }


    public function test_read_config_file_with_custom_handler()
    {
        $config = ResolveConfig::resolve(['test' => ['handler' => NullHandler::class]]);

        $this->assertTrue( $config['channels']['test']['handler'] == 'Monolog\Handler\NullHandler' );
    }


    public function test_read_config_file_assert_default_config()
    {
        $config = ResolveConfig::resolve(['test' => ['handler' => RotatingFileHandler::class]]);

        $this->assertTrue( isset($config['channels']['test']['config']) );
    }


    public function test_read_config_file_assert_default_severity()
    {
        $config = ResolveConfig::resolve(['test' => ['handler' => RotatingFileHandler::class]]);

        $this->assertTrue( $config['channels']['test']['config']['level'] == \Monolog\Logger::DEBUG );
    }


    public function test_read_config_file_assert_default_formatter()
    {
        $config = ResolveConfig::resolve(['test' => ['handler' => RotatingFileHandler::class]]);

        $this->assertTrue( get_class($config['channels']['test']['formatter']) == 'Monolog\Formatter\LineFormatter' );
    }


    public function test_read_config_file_assert_default_processor()
    {
        $config = ResolveConfig::resolve(['test' => ['handler' => RotatingFileHandler::class]]);

        $this->assertTrue( get_class($config['channels']['test']['processor']) == 'Monolog\Processor\PsrLogMessageProcessor' );
    }


    public function test_read_config_file_with_invalid_handler()
    {
        $this->expectException(LoggerConfigException::class);

        $config = ResolveConfig::resolve(['test' => ['handler' => DummyHandler::class]]);
    }
}


class DummyHandler {

}