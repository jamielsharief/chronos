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

use Chronos\Cmd\Database\MySQL;
use PHPUnit\Framework\TestCase;

final class MySQLTest extends TestCase
{
    protected $testConfig = [
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => 'root'
    ];

    public function testBackup()
    {
        $database = new MySQL($this->testConfig);
        $this->assertEquals(
            "mysqldump --single-transaction --flush-privileges --host='127.0.0.1' --port='3306' --user='root' --password='root' 'bookmarks' --result-file='/backups/bookmarks.sql'",
             $database->backup('bookmarks', '/backups/bookmarks.sql')
        );
    }

    public function testRestore()
    {
        $database = new MySQL($this->testConfig);
        $this->assertEquals(
            "mysql --host='127.0.0.1' --port='3306' --user='root' --password='root' '/backups/bookmarks.sql' < 'bookmarks'",
             $database->restore('bookmarks', '/backups/bookmarks.sql')
        );
    }
}
