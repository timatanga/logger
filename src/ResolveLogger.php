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

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\Handler;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\PsrLogMessageProcessor;
use timatanga\Logger\Exceptions\ResolveLoggerException;

class ResolveLogger
{

    /**
     * Loaded Configuration
     * 
     * @var string
     */
    protected $config = [];

    /**
     * Logger channel
     * 
     * @var string
     */
    protected $channel;

    /**
     * Handler Arguments
     * 
     * @var array
     */
    protected $arguments = [];


    /**
     * Create a new class instance.
     *
     * @param array $custom
     * @return void
     */
    public function __construct( array $custom = [] )
    {
        // read configuration file and append custom config
        $this->config = ResolveConfig::resolve($custom);

        // create logpath if not exists
        if (! is_dir($this->config['logPath']))
            mkdir($this->config['logPath'], 0777, true);
    }


    /**
     * Append Custom Config
     *
     * @param array  $custom
     * @return $this
     */
    public function appendConfig( array $custom = [] )
    {
        // read configuration file and append custom config
        $this->config = ResolveConfig::resolve($custom);

        return $this;
    }


    /**
     * Set Logger Channel
     * 
     * This method sets the given channel if it is found in the loaded configuration.
     * By reflecting the handlers constructor, its arguments and default values are 
     * extracted and merged against the channel configuration attributes.
     *
     * @param string  $handler
     * @return $this
     */
    public function setChannel( $channel )
    {
        if (! isset($this->config['channels'][$channel]) )
            throw new ResolveLoggerException('Failed to set channel. Configuration for channel "' .$channel. '" not found.');

        // set logger channel
        $this->channel = $channel;

        // extract handler class
        $handler = $this->config['channels'][$this->channel]['handler'];

        // resolve logger handler constructor parameters
        $parameters = $this->resolveContructorParameters($handler);

        // extract channels handler config
        $config = $this->config['channels'][$channel]['config'] ?? [];

        // build logger handler arguments which can later get passed into constructor.
        $this->arguments = $this->buildHandlerArguments($parameters, $config);

        return $this;
    }


    /**
     * Build Handler Arguments
     * 
     * Each Logger Handler has it's own constructor arguments. The parameters argument
     * carries the handlers parameters as well as its default values. For each
     * parameter the configured values or default values are set.
     *
     * @param array  $parameters  array of ReflectionParameters
     * @param array  $config
     * @return $this
     */
    protected function buildHandlerArguments( array $parameters = [], array $config = [] )
    {
        $arguments = [];

        foreach ($parameters as $parameter) {
            
            // extract param name and default value
            [$name, $default] = $this->extractParameters($parameter);

            // set param to given config value or use default as fallback
            $parameter = $config[$name] ?? $default;

            // prefix logfile with logpath
            if ( $name == 'filename' && isset($config['filename']) )
                $parameter = strpos($config['filename'], DIRECTORY_SEPARATOR) !== false ? 
                    $config['filename'] : $this->config['logPath'].$config['filename'];

            // prefix logfile with logpath
            if ( $name == 'stream' && isset($config['stream']) )
                $parameter = strpos($config['stream'], DIRECTORY_SEPARATOR) !== false ? 
                    $config['stream'] : $this->config['logPath'].$config['stream'];

            // set default logfile
            if ( ($name == 'filename' || $name == 'stream') && ( !isset($config['filename']) && !isset($config['stream']) ) ) 
                $parameter = $this->config['logFile'];

            $arguments[] = $parameter;
        }

        return $arguments;
    }


    /**
     * Create Logger Handler Instance
     *
     * @return Handler
     */
    public function createInstance()
    {
        try {

            if ( empty($this->arguments) )
                throw new ResolveLoggerException('Can not create handler instance. Please use setConfig or setHandler upfront.');

            // extract handler class
            $handler = $this->config['channels'][$this->channel]['handler'];

            // extract formatter class
            $formatter = $this->config['channels'][$this->channel]['formatter'];

            // extract processor class
            $processor = $this->config['channels'][$this->channel]['processor'];

            // instantiate new handler class
            $instance = new $handler( ...$this->arguments );

            // append formatter to handler
            if ( $instance instanceof FormattableHandlerInterface && !is_null($formatter) )
                $instance->setFormatter(new $formatter);

            // append processor
            if ( $instance instanceof ProcessableHandlerInterface && !is_null($processor) )
                $instance->pushProcessor(new $processor);

            return $instance;

        } catch( \Throwable $e ) {

            throw new ResolveLoggerException ($e->getMessage());
        }
    }


    /**
     * Get Configuration
     *
     * @param string  $key
     * @return mixed
     */
    public function getConfiguration( string $key = null )
    {
        if ( is_null($key) )
            return $this->config;

        if (! isset($this->config[$key]) )
            throw new ResolveLoggerException('Configuration key not found. Key: ' . $key);

        return $this->config[$key];
    }


    /**
     * Get Handler Arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }


    /**
     * Resolve Constructor Parameters
     *
     * @param AbstractHandler  $handler
     * @return void
     */
    protected function resolveContructorParameters( $handler )
    {
        $class = new \ReflectionClass($handler);

        if (! in_array('Monolog\Handler\HandlerInterface', array_keys($class->getInterfaces())) )
            throw new ResolveLoggerException('Given handler is not instance of HandlerInterface: ' .$handler);

        $constructor = $class->getConstructor();

        return $constructor->getParameters();
    }


    /**
     * Extract Parameters
     *
     * @param object $parameter
     * @return array
     */
    protected function extractParameters( $parameter )
    {
        // get ReflectionParameter attributes
        $name = $parameter->getName() ?? null;
        $default = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() :  null;
        $type = $parameter->getType() ? $parameter->getType()->__toString() : null;

        // cast parameter to predefined typeHint
        $default = $this->castParameter($default, $type);

        return [$name, $default];
    }


    /**
     * Cast Parameter to predefined typeHint
     *
     * @param mixed $parameter
     * @param string $type
     * @return array
     */
    protected function castParameter( $parameter, $type )
    {
        // typeHint default parameters
        if ( $type == 'string' ) 
            return (string) $parameter;

        if ( $type == 'int' ) 
            return (int) $parameter;

        if ( $type == 'bool' ) 
            return (bool) $parameter;

        return $parameter;
    }

}