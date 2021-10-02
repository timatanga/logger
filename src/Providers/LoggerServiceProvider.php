<?php

/*
 * This file is part of the Logger package.
 *
 * (c) Mark Fluehmann dbiz.apps@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace timatanga\Logger\Providers;

use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }


    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // publish config file
        $this->publishes([
            __DIR__.'/../../config/logger.php' => config_path('logger.php')
        ], 'config');
    }
}
