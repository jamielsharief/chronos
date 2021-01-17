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

class Zip extends BaseCompression
{
    public function __construct()
    {
        if (! $this->isSupported()) {
            throw new RuntimeException('Zip is not installed.');
        }
    }
    
    /**
     * @param string $path e.g. /backups/mysql.sql
     * @return string
     */
    public function compress(string $path): string
    {
        return sprintf('zip -9jm %s %s',
            escapeshellarg($path . '.' . $this->extension()),
            escapeshellarg($path),
        );
    }

    /**
     * @param string $path /backups/mysql.sql.gz
     * @return string
     */
    public function uncompress(string $path): string
    {
        $destination = pathinfo($path, PATHINFO_DIRNAME);

        return sprintf('unzip -j %s -d %s && rm %s',
         escapeshellarg($path),
         escapeshellarg($destination),
         escapeshellarg($path)
        );
    }

    /**
     * @return string
     */
    public function extension(): string
    {
        return 'zip';
    }

    /**
     * Checks that OpenSSL is installed, in theory it needs v1.1.1. On MacOS LibreSSL is included
     * and that does not support pbkdf2 iter
     *
     * @return boolean
     */
    private function isSupported(): bool
    {
        exec('zip --version 2>&1', $output, $code);

        $result = $output[0] ?? false;
   
        return $result !== false && $code === 0 && preg_match('/Info-ZIP/', $result);
    }
}
