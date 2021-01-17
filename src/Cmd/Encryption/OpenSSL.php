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
 *
 * Mac uses libressl which does not support pbkdf2 or iter
 *
 * $ brew update
 * $ brew install openssl
 * # if it is already installed, update it:
 * $ brew upgrade openssl@1.1
 *
 * @see https://askubuntu.com/questions/1093591/how-should-i-change-encryption-according-to-warning-deprecated-key-derivat
 * @link https://www.openssl.org/docs/man1.1.1/man1/enc.html
 */
class OpenSSL extends BaseEncryption
{
    public function __construct()
    {
        if (! $this->isSupported()) {
            throw new RuntimeException('OpenSSL is not installed.'); // LibreSSL does not count
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
        $result = exec('openssl version 2>&1', $output, $code);

        return $result !== false && $code === 0 && preg_match('/^OpenSSL/', $result);
    }

    /**
     * @param string $path
     * @param string $password
     * @return string
     */
    public function encrypt(string $path, string $password): string
    {
        return sprintf('openssl enc -e -aes-256-cbc -md sha512 -pbkdf2 -iter 100100 -salt -in %s -out %s -k %s && rm %s',
            escapeshellarg($path),
            escapeshellarg($path . '.' . $this->extension()),
            $password,
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
        return sprintf('openssl enc -d -aes-256-cbc -md sha512 -pbkdf2 -iter 100100 -in %s -out %s -k %s && rm %s',
            escapeshellarg($path),
            escapeshellarg(substr($path, 0, -4)), // remove .aes
            $password,
            escapeshellarg($path)
        );
    }

    /**
     * @return string
     */
    public function extension(): string
    {
        return 'enc';
    }
}
