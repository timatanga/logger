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
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Processor\PsrLogMessageProcessor;
use timatanga\Logger\Exceptions\LoggerConfigException;
use Monolog\Logger as MonologLogger;

class ResolveConfig
{

    /**
     * The logger configuration file
     * 
     * @var string
     */
    protected static $configFile = 'logger.php';

    /**
     * The logger configuration path
     * 
     * @var string
     */
    protected static $configPath = 'timatanga/logger/config/';

    /**
     * The standard logfile use when writing logs.
     * It gets overwritten when set in configuration file
     *
     * @var string
     */
    protected static $logFile = 'log';

    /**
     * The standard path for logfile
     * It gets overwritten when set in configuration file
     *
     * @var string
     */
    protected static $logPath = 'storage/logs';

    /**
     * The standard date format to use when writing logs.
     * It gets overwritten when set in configuration file
     *
     * @var string
     */
    protected static $dateFormat = 'Y-m-d H:i:s';

    /**
     * The standard timezone to use when writing logs.
     * It gets overwritten when set in configuration file
     *
     * @var string
     */
    protected static $timezone = 'Europe/Berlin';

    /**
     * The Log severities.
     *
     * @var array
     */
    protected static $severities = [
        'debug' => MonologLogger::DEBUG,
        'info' => MonologLogger::INFO,
        'notice' => MonologLogger::NOTICE,
        'warning' => MonologLogger::WARNING,
        'error' => MonologLogger::ERROR,
        'critical' => MonologLogger::CRITICAL,
        'alert' => MonologLogger::ALERT,
        'emergency' => MonologLogger::EMERGENCY,
    ];
    

    /**
     * Resolve logger configuration
     * 
     * The custom argument allows for custom configurations. This is useful for 
     * extending the logger configuration file or when not having a config
     * file at all and the custom options are the only configs available
     *
     * @param array  $custom
     * @return $this
     */
    public static function resolve( array $custom = [] )
    {
        $config = self::readConfigFile();

        $config = self::setConfigurationDefaults($config);

        $config = self::mergeCustomChannels($config, $custom);

        $config = self::sanitizeConfiguration($config);

        return $config;
    }


    /**
     * Validate Channel Configuration
     * 
     * Assuming an existing configuration, this method allows for 
     * validating and sanitizing the channel configuration
     *
     * @param array  $channel
     * @return array
     */
    public static function sanitizeChannel( array $channel )
    {
        $channel = self::validateHandler($channel);

        $channel = self::sanitizeFormatter($channel);

        $channel = self::sanitizeProcessor($channel);

        $channel = self::sanitizeSeverity($channel);

        return $channel;
    }


    /**
     * Read Configuration File
     * 
     * First try to read from an application config directory including the config file.
     * Second try to read configuration file from the package config directory.
     * If both tries failed, return null. 
     * 
     * @return string|null
     */
    protected static function readConfigFile()
    {
        // config file
        $file = self::$configFile;

        // first try root config path
        $rootConfigPath = config_path($file);

        if ( fileExists($rootConfigPath) )
            return require $rootConfigPath;

        // local config path
        $localConfigPath = root_path(self::$configPath.$file);

        if ( fileExists($localConfigPath) )
            return require $localConfigPath;

        return null;
    }


    /**
     * Set Configuration Defaults
     *
     * @return array
     */
    protected static function setConfigurationDefaults( array $config )
    {
        if (! isset($config['dateFormat']) )
            $config['dateFormat'] = self::$dateFormat;

        if (! isset($config['timezone']) )
            $config['timezone'] = self::$timezone;

        if (! isset($config['logFile']) )
            $config['logFile'] = self::$logFile;

        if (! isset($config['logPath']) )
            $config['logPath'] = self::$logPath;

        if (! isset($config['channels']) )
            $config['channels'] = [];

        if (! fileExists($config['logFile']) ) {
            $config['logPath'] = root_path($config['logPath']).DIRECTORY_SEPARATOR;
            $config['logFile'] = $config['logPath'].$config['logFile'];
        }

        return $config;
    }


    /**
     * Merge Custom Channel Configuration
     * 
     * First try to read from an application config directory including the config file.
     * Second try to read configuration file from the package config directory.
     * If both tries failed, return null. 
     * 
     * @param array  $config   config read from the configuration file
     * @param array  $custom   custom channel configuration
     * @return string|null
     */
    protected static function mergeCustomChannels( array $config, array $custom = [] )
    {
        if ( empty($custom) )
            return $config;

        // loop over custom configuration
        foreach ($custom as $channel => $arg) {
            
            if (! is_string($channel) )
                throw new LoggerConfigException('Custom channel configuration does not fulfill required structure');

            if (! isset($arg['handler']) )
                throw new LoggerConfigException('Channel configuration requires a handler, none given');            
        
            $config['channels'][$channel] = $arg;
        }

        return $config;
    }


    /**
     * Validate Configuration
     *
     * @param array  $config
     * @return array
     */
    protected static function sanitizeConfiguration( array $config )
    {
        foreach ($config['channels'] as $channel => $arguments) {

            $config['channels'][$channel] = self::validateHandler($config['channels'][$channel]);

            $config['channels'][$channel] = self::sanitizeFormatter($config['channels'][$channel]);

            $config['channels'][$channel] = self::sanitizeProcessor($config['channels'][$channel]);

            $config['channels'][$channel] = self::sanitizeSeverity($config['channels'][$channel]);
        }

        return $config;
    }


    /**
     * Validate Channel Handler Configuration
     *
     * @param array  $channel
     * @return array
     */
    protected static function validateHandler( array $channel )
    {
        if (! isset($channel['handler']) )
            throw new LoggerConfigException('Channel configuration requires a handler, none given');            

        $class = new \ReflectionClass($channel['handler']);

        if (! in_array('Monolog\Handler\HandlerInterface', array_keys($class->getInterfaces())) )
            throw new LoggerConfigException('Given handler does not implement HandlerInterface: ' .$channel['handler']);

        return $channel;
    }


    /**
     * Sanitize Channel Formatter Configuration
     *
     * @param array  $channel
     * @return array
     */
    protected static function sanitizeFormatter( array $channel )
    {
        if ( isset($channel['formatter']) && !in_array('Monolog\Formatter\FormatterInterface', class_implements($channel['formatter'])) )
            throw new LoggerConfigException('Given formatter does not implement FormattableHandlerInterface: ' .$channel['formatter']);

        if (! isset($channel['formatter']) )
            $channel['formatter'] = self::defaultFormatter();   

        return $channel;
    }


    /**
     * Sanitize Channel Processor Configuration
     *
     * @param array  $channel
     * @return array
     */
    protected static function sanitizeProcessor( array $channel )
    {
        if ( isset($channel['processor']) && !in_array('Monolog\Handler\ProcessableHandlerInterface', class_implements($channel['processor'])))
            throw new LoggerConfigException('Given processor does not implement ProcessableHandlerInterface: ' .$channel['processor']);

        if (! isset($channel['processor']) )
            $channel['processor'] = self::defaultProcessor();   

        return $channel;
    }


    /**
     * Sanitize Channel Severity
     *
     * @param array  $channel
     * @return array
     */
    protected static function sanitizeSeverity( array $channel )
    {
        if (! isset($channel['config']) )
            $channel['config'] = [];

        if (! isset($channel['config']['level']) ) {
            $channel['config'] = ['level' => self::$severities[env('LOG_LEVEL', 'debug')]];
        
        } else {
            $severity = isset(self::$severities[$channel['config']['level']]) ? 
                            self::$severities[$channel['config']['level']] : env('LOG_LEVEL', 'debug');

            $channel['config']['level'] = $severity;
        }

        return $channel;
    }


    /**
     * Get a Monolog formatter instance.
     *
     * @return \Monolog\Formatter\LineFormatter
     */
    protected static function defaultFormatter()
    {
        $formatter =  new LineFormatter(null, self::$dateFormat, true, true);

        $formatter->includeStacktraces();
        
        return $formatter;
    }


    /**
     * Get a Monolog processor instance.
     *
     * @return \Monolog\Processor\PsrLogMessageProcessor
     */
    protected static function defaultProcessor()
    {
        return new PsrLogMessageProcessor(self::$dateFormat, true);
    }

}