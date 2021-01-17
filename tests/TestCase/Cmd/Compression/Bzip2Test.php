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
use Chronos\Cmd\Compression\Bzip2;

final class Bzip2Test extends TestCase
{
    public function testCompress()
    {
        $compression = new Bzip2();
        $this->assertEquals("bzip2 -z9 '/backups/dump.sql'", $compression->compress('/backups/dump.sql'));
    }

    public function testUnCompress()
    {
        $compression = new Bzip2();
        $this->assertEquals("bzip2 -d '/backups/dump.sql.bz2'", $compression->uncompress('/backups/dump.sql.bz2'));
    }

    public function testExtension()
    {
        $this->assertEquals('bz2', (new Bzip2())->extension());
    }
}
