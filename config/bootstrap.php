<?php
/**
 * Chronos Database Backup and Restore
 * Copyright 2021 Jamiel Sharief.
 *
 * Licensed under The Apache License 2.0
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/Apache-2.0 Apache License 2.0
 */

use Origin\Cache\Cache;
use Origin\Core\Config;
use Origin\Console\ErrorHandler;

/**
 * Configure PATH Constants and setup the application
 * namespace so that integration testing can work
 */
define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/src');

// Work with composer copy to bin
if (file_exists(getcwd() . '/vendor/autoload.php')) {
    require getcwd() . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

(new ErrorHandler())->register();

Config::write('App.namespace', 'Chronos');
Config::write('App.debug', false);

Cache::config('origin', [
    'className' => FileEngine::class,
    'path' => sys_get_temp_dir(),
    'duration' => '+2 minutes',
    'prefix' => 'cache_',
    'serialize' => true
]);
