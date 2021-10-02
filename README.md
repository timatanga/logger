# Logger
To keep track what is going on in your application a logging service is very helpful. Depending on your needs you can write logs to files or send it to web services to notify your stackholders. This package utilizes the Monolog library and therefor implements the PSR-3 interface that you can type-hint against in your own libraries to keep a maximum of interoperability. 

The logging is based on channels. Each channel can have it's own "log destination" or formatting depending on a log severity. It's possible to use different channels at one time to provide informations on different subjects.



## Installation
composer require timatanga/logger



## Basic Configuration
The Logger packages ships with a logger configuration which you can use, extend or change upon your needs. It's housed in the config/logger.php configuration file and consists basically of two parts. The first part are basis logging configurations unrelated to any logging channels.

	// config/logger.php

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

	    ...
	]


## Channel Configuration
The second part hosts the channel configurations. The configuration of logging channels follows a simple structure. A channel is identified by its key and requires at least a handler. All other arguments are optional.
In the example below is a configuration for the channel "main"

	// config/logger.php

	return [

		....

	    /**
	     * Channels
	     * Preconfigured log channels
	     */
	    'channels' => [

	        'main' => [
	            'handler' => RotatingFileHandler::class,
	            'formatter' => LinePrettyFormatter::class,
	            'processor' => 
	            'config' => [
	                'filename' => 'log',
	                'maxFiles' => 10,
	                'level' => env('LOG_LEVEL_MAIN', 'debug'),
	            ]
	        ],
		]
	]    

As mentioned earlier each channel needs at least a handler which can be one of the provided Monolog handlers, an extension of existing handlers or one of your own. A handler must implement the `Monolog\Handler\HandlerInterface` to get accepted.

Setting a formatter for a channel is considered optional. If no formatter is given the `Monolog\Formatter\LineFormatter` is set as default. If you wish to replace the formatter or build one of your one, please consider that is must implement the `Monolog\Formatter\FormatterInterface`.

With Processors Monolog transforms logging messages or integrates further datasets. Without explicitly set a processor for a channel, the `Monolog\Processor\PsrLogMessageProcessor`is appended to the handler instance. This processor has the ability to 
processes a log record’s message according to PSR-3 rules, replacing {foo} with the value from $context['foo'].

The config section of a channel configuration hosts the individual handler parameters. Each handler has it's own requirement when creating a new instance. Please consider the handler documentation or source code to get to know the according parameters.



## Usage
Taking advantage of the logger package is easy and straightforward. First you need to create a new instance of the logger.
The constructor accepts a single channel as a string or set of multiple channels as array argument

        $logger = new Logger('main');
        $logger = new Logger(['main', 'error', 'slack']);

When one of the requested channels is not provided in the configuration file you'll except an `timatanga\Logger\Exceptions\ResolveLoggerException` to be thrown.


Imagine circumstance where you cannot or are not willing to change the logger configuration but need a further logger channel and its configuration to get into play. To avoid an exception to be thrown, you may pass a custom channel configuration into the Logger constructor like so.

        $logger = new Logger(['main', extension'], ['extension' => ['handler' => RotatingFileHandler::class]]);

In the example above the channel "main" is registered as it's configuration is housed in the config/logger.php configuration file. Further the custom channel "extension" is registered while it's configuration is provided in the Logger constructor.


Sending log messages now very easy. You just need to mention the severity level with it's log message.

        $logger->info("just an info");

You may want to combine the log message with contextual data:

        $logger->info("just an info {key}", ['key' => 'message']);

        // will log: "just an info message"
