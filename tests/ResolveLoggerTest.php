<?php

namespace Tests;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use PHPUnit\Framework\TestCase;
use timatanga\Logger\ResolveLogger;

class ResolveLoggerTest extends TestCase
{

    public function test_resolve_null_logger()
    {
        $channel = 'null';

        $resolver = new ResolveLogger;

        $this->assertTrue( $resolver->setChannel($channel)->createInstance() instanceof NullHandler );
    }


    public function test_resolve_main_logger()
    {
        $channel = 'main';

        $resolver = new ResolveLogger;

        $this->assertTrue( $resolver->setChannel($channel)->createInstance() instanceof RotatingFileHandler );
    }


    public function test_resolve_custom_config_logger()
    {
        $channel = 'test';

        $resolver = new ResolveLogger(['test' => ['handler' => SyslogHandler::class]]);

        $this->assertTrue( $handler = $resolver->setChannel($channel)->createInstance() instanceof SyslogHandler );
    }

    public function test_resolve_append_custom_config_logger()
    {
        $channel = 'test';

        $resolver = new ResolveLogger;

        $this->assertTrue( $handler = $resolver->appendConfig((['test' => ['handler' => SyslogHandler::class]]))->setChannel($channel)->createInstance() instanceof SyslogHandler );
    }
}