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
use Chronos\Cmd\Compression\Gzip;

final class GzipTest extends TestCase
{
    public function testCompress()
    {
        $compression = new Gzip();
        $this->assertEquals("gzip -9 '/backups/dump.sql'", $compression->compress('/backups/dump.sql'));
    }

    public function testUnCompress()
    {
        $compression = new Gzip();
        $this->assertEquals("gzip -d '/backups/dump.sql.gz'", $compression->uncompress('/backups/dump.sql.gz'));
    }

    public function testExtension()
    {
        $this->assertEquals('gz', (new Gzip())->extension());
    }
}
