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

use Chronos\Cmd\Encryption\Gpg;
use PHPUnit\Framework\TestCase;

final class GPGTest extends TestCase
{
    public function testEncrypt()
    {
        $this->assertEquals(
            "gpg -c --cipher-algo AES256 --batch --passphrase secret '/backups/dump.sql' && rm '/backups/dump.sql'",
            (new Gpg())->encrypt('/backups/dump.sql', 'secret'));
    }

    public function testDecrypt()
    {
        $this->assertEquals(
            "gpg -d -o '/backups/dump.sql' --batch --passphrase secret '/backups/dump.sql.gpg' && rm '/backups/dump.sql.gpg'",
            (new Gpg())->decrypt('/backups/dump.sql.gpg', 'secret'));
    }

    public function testExtension()
    {
        $this->assertEquals('gpg', (new Gpg())->extension());
    }
}
