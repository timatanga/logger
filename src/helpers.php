<?php

/*
 * This file is part of the Logger package.
 *
 * (c) Mark Fluehmann dbiz.apps@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


if (! function_exists('root_path')) {

    /**
     * Get application root path
     *
     * @param  string  $path
     * @return path
     */
    function root_path( string $path = null )
    {
        $currentDir = dirname(__DIR__);

        // if path contains vendor, it's assumed that package is installed via composer
        if ( strpos($currentDir, 'vendor') !== false )
            $dir = dirname(__DIR__, 4);

        // if path does not contain vendor, it's assumed that package is in development
        if ( strpos($currentDir, 'vendor') == false )
            $dir = dirname(__DIR__, 3);

        // append given path to root directory
        if (! is_null($path) )
            $dir = $dir .DIRECTORY_SEPARATOR.$path;

        return $dir;
    }
}


if (! function_exists('config_path')) {

    /**
     * Get configuration path
     *
     * @param  string  $path
     * @return path
     */
    function config_path( string $path = null )
    {
        // set config path
        $dir = root_path('config');

        // append given path to config directory
        if (! is_null($path) )
            $dir = $dir .DIRECTORY_SEPARATOR.$path;

        return $dir;
    }
}


if (! function_exists('storage_path')) {

    /**
     * Get storage path
     *
     * @param  string  $path
     * @return path
     */
    function storage_path( string $path = null )
    {
        // set config path
        $dir = root_path('storage');

        // append given path to config directory
        if (! is_null($path) )
            $dir = $dir .DIRECTORY_SEPARATOR.$path;

        return $dir;
    }
}


if (! function_exists('fileExists')) {

    /**
     * Determine if file exists
     *
     * @param string  $file
     * @return bool
     */
    function fileExists( string $file)
    {
        if (!is_file($file) || !file_exists($file))
            return false;

        return true;
    }
}


if (! function_exists('env')) {

    /**
     * Read environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $env = root_path('.env');

        if ( !fileExists($env) || !is_readable($env) )
            return $default;

        // read environment file line by line
        $lines = file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {

            // exclude uncommented lines
            if (strpos(trim($line), '#') === 0)
                continue;

            [$name, $value] = explode('=', $line, 2);

            $name = trim($name);
            $value = trim($value);

            if ( $name == $key )
                return $value;
        }

        return $default;
    }

}


