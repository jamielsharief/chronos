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

class Postgres extends BaseDatabase
{
    /**
     * @var array
     */
    protected $defaultConfig = [
        'host' => 'localhost',
        'port' => 5432,
        'username' => 'postgres',
        'password' => 'password'
    ];

    /**
     * Returns the backup command for Postgress
     *
     * @internal clean is needed so drop commands are added, if not when restoring to an non
     * empty database you get mixed records (so it works like MySQL)
     *
     * @param string $database database name
     * @param string $path
     * @return string
     */
    public function backup(string $database, string $path): string
    {
        return sprintf(
            'PGPASSWORD=%s pg_dump --clean --host=%s --port=%s --username=%s %s --file=%s',
            escapeshellarg($this->config['password']),
            escapeshellarg($this->config['host']),
            escapeshellarg((string) $this->config['port']),
            escapeshellarg($this->config['username']),
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
            'PGPASSWORD=%s psql --host=%s --port=%s --user=%s %s < %s',
            escapeshellarg($this->config['password']),
            escapeshellarg($this->config['host']),
            escapeshellarg((string) $this->config['port']),
            escapeshellarg($this->config['username']),
            escapeshellarg($database),
            escapeshellarg($path)
        );
    }
}
