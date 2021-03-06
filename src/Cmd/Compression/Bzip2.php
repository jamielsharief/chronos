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

class Bzip2 extends BaseCompression
{
    public function __construct()
    {
        if (! $this->isSupported()) {
            throw new RuntimeException('Bzip2 is not installed.');
        }
    }
    
    /**
     * @param string $path e.g. /backups/mysql.sql
     * @return string
     */
    public function compress(string $path): string
    {
        return sprintf('bzip2 -z9 %s',
            escapeshellarg($path),
        );
    }

    /**
     * @param string $path /backups/mysql.sql.gz
     * @return string
     */
    public function uncompress(string $path): string
    {
        return sprintf('bzip2 -d %s', escapeshellarg($path));
    }

    /**
     * @return string
     */
    public function extension(): string
    {
        return 'bz2';
    }

    /**
     * @return boolean
     */
    private function isSupported(): bool
    {
        exec('bzip2 --help 2>&1', $output, $code);

        $result = $output[0] ?? false;
   
        return $result !== false && $code === 0 && preg_match('/^bzip2,/', $result);
    }
}
