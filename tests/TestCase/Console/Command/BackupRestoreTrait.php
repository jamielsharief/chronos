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
declare(strict_types = 1);
namespace Chronos\Test\TestCase\Console\Command;

trait BackupRestoreTrait
{
    protected $backupDirectory;

    protected function beforeTest()
    {
        $this->backupDirectory = sys_get_temp_dir() . '/' . uniqid();
        mkdir($this->backupDirectory, 0775);
      
        $config = [
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'backupDirectory' => $this->backupDirectory,
            'databaseDirectory' => getcwd(),
            'engine' => env('DB_ENGINE', 'mysql') # Databse type e.g. mysql, sqlite etc.
        ];

        file_put_contents($this->configPath(), json_encode($config));
    }

    protected function configPath()
    {
        return getcwd() . '/chronos.json';
    }

    protected function afterTest(): void
    {
        if (file_exists($this->configPath())) {
            unlink($this->configPath());
        }
    }
}
