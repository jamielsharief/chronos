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
use Chronos\Cmd\Compression\SevenZip;

final class SevenZipTest extends TestCase
{
    public function testCompress()
    {
        $compression = new SevenZip();
        $this->assertEquals("7z a -mx=9 '/backups/dump.sql.7z' '/backups/dump.sql' && rm '/backups/dump.sql'", $compression->compress('/backups/dump.sql'));
    }

    public function testUnCompress()
    {
        $compression = new SevenZip();
        $this->assertEquals("7z e '/backups/dump.sql.gz'", $compression->uncompress('/backups/dump.sql.gz'));
    }

    public function testExtension()
    {
        $this->AssertEquals('7z', (new SevenZip())->extension());
    }
}
