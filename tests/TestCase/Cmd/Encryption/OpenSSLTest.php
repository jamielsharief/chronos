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

namespace Chronos\Test\TestCase\Cmd\Compression;

use PHPUnit\Framework\TestCase;
use Chronos\Cmd\Encryption\OpenSSL;

final class OpenSSLTest extends TestCase
{
    public function testEncrypt()
    {
        $this->assertEquals(
            "openssl enc -e -aes-256-cbc -md sha512 -pbkdf2 -iter 100100 -salt -in '/backups/dump.sql' -out '/backups/dump.sql.enc' -k secret && rm '/backups/dump.sql'",
            (new OpenSSL())->encrypt('/backups/dump.sql', 'secret'));
    }

    public function testDecrypt()
    {
        $this->assertEquals(
            "openssl enc -d -aes-256-cbc -md sha512 -pbkdf2 -iter 100100 -in '/backups/dump.sql.enc' -out '/backups/dump.sql' -k secret && rm '/backups/dump.sql.enc'",
            (new OpenSSL())->decrypt('/backups/dump.sql.enc', 'secret'));
    }

    public function testExtension()
    {
        $this->assertEquals('enc', (new OpenSSL())->extension());
    }
}
