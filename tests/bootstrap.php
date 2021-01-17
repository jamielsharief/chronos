<?php

use Origin\Cache\Cache;
use Origin\Core\Config;
use Chronos\BackupRestore;
use Origin\Cache\Engine\FileEngine;
use Origin\Model\ConnectionManager;

/**
 * Constants for file paths are configured here
 */
if (! defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');
define('CONFIG', ROOT . '/config');
define('DATABASE', ROOT . '/database');
define('PLUGINS', ROOT . '/plugins');
define('TESTS', ROOT . '/tests');
define('WEBROOT', ROOT . '/public');
define('TMP', ROOT . '/tmp');
define('LOGS', ROOT . '/logs');
define('CACHE', TMP . '/cache');
define('STORAGE', ROOT . '/storage');

require dirname(__DIR__) . '/vendor/originphp/core/bootstrap.php';

Config::write('App.debug', env('APP_DEBUG', true));
Config::write('App.namespace', 'Chronos');

ConnectionManager::config('default', [
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'engine' => env('DB_ENGINE', 'mysql')
]);

ConnectionManager::config('test', [
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'engine' => env('DB_ENGINE', 'mysql')
]);

Cache::config('origin', [
    'className' => FileEngine::class,
    'path' => sys_get_temp_dir(),
    'duration' => '+2 minutes',
    'prefix' => 'cache_',
    'serialize' => true
]);

$tmpPath = sys_get_temp_dir() . '/backups';
if (! is_dir($tmpPath)) {
    mkdir($tmpPath);
}

BackupRestore::config('default', [
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'backupDirectory' => $tmpPath,
    'databaseDirectory' => dirname(__DIR__) . '/data',
    'engine' => env('DB_ENGINE', 'mysql') # Databse type e.g. mysql, sqlite etc.
]);
