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
use Chronos\Cmd\Database\Postgres;

final class PostgresTest extends TestCase
{
    protected $testConfig = [
        'host' => '127.0.0.1',
        'port' => 5432,
        'user' => 'root',
        'password' => 'root'
    ];

    public function testBackup()
    {
        $database = new Postgres($this->testConfig);
        $this->assertEquals(
            "PGPASSWORD='root' pg_dump --clean --host='127.0.0.1' --port='5432' --username='postgres' 'bookmarks' --file='/backups/bookmarks.sql'",
             $database->backup('bookmarks', '/backups/bookmarks.sql')
        );
    }

    public function testRestore()
    {
        $database = new Postgres($this->testConfig);
        $this->assertEquals(
            "PGPASSWORD='root' psql --host='127.0.0.1' --port='5432' --user='postgres' 'bookmarks' < '/backups/bookmarks.sql'",
             $database->restore('/backups/bookmarks.sql', 'bookmarks')
        );
    }
}
