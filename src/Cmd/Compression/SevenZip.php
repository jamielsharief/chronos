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
namespace Chronos\Cmd\Compression;

use RuntimeException;

/**
 * To install on ubuntu
 * $ apt install pz7ip-full
 */
class SevenZip extends BaseCompression
{
    public function __construct()
    {
        if (! $this->isSupported()) {
            throw new RuntimeException('7zip is not installed.');
        }
    }

    /**
     * @param string $path e.g. /backups/mysql.sql
     * @return string
     */
    public function compress(string $path): string
    {
        $escaped = escapeshellarg($path);

        return sprintf('7z a -mx=9 %s %s && rm %s',
            escapeshellarg($path .'.7z'),
            $escaped,
            $escaped,
        );
    }

    /**
     * @param string $path /backups/mysql.sql.gz
     * @return string
     */
    public function uncompress(string $path): string
    {
        return sprintf('7z e %s', escapeshellarg($path));
    }

    /**
     * @return string
     */
    public function extension(): string
    {
        return '7z';
    }

    /**
     * @internal 7zip has strange output
     * @return boolean
     */
    private function isSupported(): bool
    {
        exec('7z --help 2>&1', $output, $code);

        $result = count($output) ? implode(PHP_EOL, $output) : false;

        return $result !== false && $code === 0 && preg_match('/p7zip/', $result);
    }
}
