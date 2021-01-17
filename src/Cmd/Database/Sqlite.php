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

class Sqlite extends BaseDatabase
{
    /**
     * @var array
     */
    protected $defaultConfig = [
        'host' => 'localhost'
    ];

    /**
     * Returns the backup command for Sqlite
     *
     * @internal There is a backup command but from what I understand this will fail if its locked, so
     * going to use dump. .output will specify the file.
     *
     * @param string $database
     * @param string $path
     * @return string
     */
    public function backup(string $database, string $path): string
    {
        return sprintf(
            '( echo .output %s ; echo .dump ) | sqlite3 %s',
            escapeshellarg($path),
            escapeshellarg($database)
        );
    }

    /**
     * Creates the restore command, using dump we can't drop tables or create similar
     * behavior, so database needs to be removed first
     *
     * @internal renaming and moving does not work get General error: 8 attempt to write a readonly
     * database, copying seems to work.
     *
     * @param string $path
     * @param string $database
     * @return string
     */
    public function restore(string $path, string $database): string
    {
        $tmpDatabase = $database . '.tmp';
    
        return sprintf(
            'rm -f %s && sqlite3 %s < %s && cp -f %s %s && rm -f %s',
            escapeshellarg($tmpDatabase),
            escapeshellarg($tmpDatabase),
            escapeshellarg($path),
            escapeshellarg($tmpDatabase),
            escapeshellarg($database),
            escapeshellarg($tmpDatabase)
        );
    }
}
