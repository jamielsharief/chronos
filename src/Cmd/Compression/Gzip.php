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

class Gzip extends BaseCompression
{
    /**
     * @param string $path e.g. /backups/mysql.sql
     * @return string
     */
    public function compress(string $path): string
    {
        return sprintf('gzip -9 %s', escapeshellarg($path));
    }

    /**
     * @param string $path /backups/mysql.sql.gz
     * @return string
     */
    public function uncompress(string $path): string
    {
        return sprintf('gzip -d %s', escapeshellarg($path));
    }

    /**
     * @return string
     */
    public function extension(): string
    {
        return 'gz';
    }
}
