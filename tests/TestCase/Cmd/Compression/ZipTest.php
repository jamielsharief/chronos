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
use Chronos\Cmd\Compression\Zip;

final class ZipTest extends TestCase
{
    public function testCompress()
    {
        $compression = new Zip();
        $this->assertEquals("zip -9jm '/backups/dump.sql.zip' '/backups/dump.sql'", $compression->compress('/backups/dump.sql'));
    }

    public function testUnCompress()
    {
        $compression = new Zip();
        $this->assertEquals("unzip -j '/backups/dump.sql.zip' -d '/backups' && rm '/backups/dump.sql.zip'", $compression->uncompress('/backups/dump.sql.zip'));
    }

    public function testExtension()
    {
        $this->assertEquals('zip', (new Zip())->extension());
    }
}
