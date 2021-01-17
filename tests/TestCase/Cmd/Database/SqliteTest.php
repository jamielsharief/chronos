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

namespace Chronos\Test\TestCase\Cmd\Database;

use PHPUnit\Framework\TestCase;
use Chronos\Cmd\Database\Sqlite;

final class SqliteTest extends TestCase
{
    protected $testConfig = [];

    public function testBackup()
    {
        $database = new Sqlite($this->testConfig);
        $this->assertEquals(
           "( echo .output '/backups/bookmarks.sql' ; echo .dump ) | sqlite3 'bookmarks.db'",
             $database->backup('bookmarks.db', '/backups/bookmarks.sql')
        );
    }

    public function testRestore()
    {
        $database = new Sqlite($this->testConfig);
        $this->assertEquals(
            "rm -f 'bookmarks.db.tmp' && sqlite3 'bookmarks.db.tmp' < '/backups/bookmarks.sql' && cp -f 'bookmarks.db.tmp' 'bookmarks.db' && rm -f 'bookmarks.db.tmp'",
             $database->restore('/backups/bookmarks.sql', 'bookmarks.db')
        );
    }
}
