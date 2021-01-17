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
namespace Chronos\Cmd\Encryption;

use RuntimeException;

/**
 * @see https://www.nas.nasa.gov/hecc/support/kb/using-gpg-to-encrypt-your-data_242.html
 */
class Gpg extends BaseEncryption
{
    public function __construct()
    {
        if (! $this->isSupported()) {
            throw new RuntimeException('GPG is not installed.');
        }
    }

    /**
     * Checks that OpenSSL is installed, in theory it needs v1.1.1. On MacOS LibreSSL is included
     * and that does not support pbkdf2 iter
     *
     * @return boolean
     */
    private function isSupported(): bool
    {
        $result = exec('gpg --version 2>&1', $output, $code);

        return $result !== false && $code === 0 && preg_match('/GnuPG/', $output[0] ?? '');
    }

    /**
     * @param string $path
     * @param string $password
     * @return string
     */
    public function encrypt(string $path, string $password): string
    {
        return sprintf('gpg -c --cipher-algo AES256 --batch --passphrase %s %s && rm %s',
         $password,
         escapeshellarg($path),
         escapeshellarg($path)
        );
    }

    /**
     * @param string $path
     * @param string $password
     * @return string
     */
    public function decrypt(string $path, string $password): string
    {
        return sprintf('gpg -d -o %s --batch --passphrase %s %s && rm %s',
            escapeshellarg(substr($path, 0, -4)), // remove .gpg
            $password,
            escapeshellarg($path),
            escapeshellarg($path)
        );
    }

    /**
     * @return string
     */
    public function extension(): string
    {
        return 'gpg';
    }
}
