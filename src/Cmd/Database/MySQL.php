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
declare(strict_types=1);
namespace Chronos\Cmd\Database;

/**
 * Use Single transaction
 */
class MySQL extends BaseDatabase
{
    /**
     * @var array
     */
    protected $defaultConfig = [
        'host' => 'localhost',
        'port' => 3306,
        'username' => 'root',
        'password' => 'password',
        'database' => null
    ];
    /**
     * Returns the backup command for MySQL
     *  -- single-transaction Starts a transaction before dumping, instead of locking and reading database
     * in current state for consistent data dump.
     *  --flush-privileges adds flush privileges at the end of dump incase user has changed
     *
     * @param string $database
     * @param string $path
     * @return string
     */
    public function backup(string $database, string $path): string
    {

      /**
        *  MYSQL_PWD - will be deprecated probably in 9?
        *  MYSQL_PWD=%s mysqldump --single-transaction --flush-privileges --host=%s --port=%s --user=%s %s > %s
       */
        return sprintf(
            'mysqldump --single-transaction --flush-privileges --host=%s --port=%s --user=%s --password=%s %s --result-file=%s',
            escapeshellarg($this->config['host']),
            escapeshellarg((string) $this->config['port']),
            escapeshellarg($this->config['username']),
            escapeshellarg($this->config['password']),
            escapeshellarg($database),
            escapeshellarg($path),
        );
    }

    /**
     * Restore command for this database
     *
     * @param string $path
     * @param string $database
     * @return string
     */
    public function restore(string $path, string $database): string
    {
        return sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
            escapeshellarg($this->config['host']),
            escapeshellarg((string) $this->config['port']),
            escapeshellarg($this->config['username']),
            escapeshellarg($this->config['password']),
            escapeshellarg($database),
            escapeshellarg($path)
        );
    }
}
