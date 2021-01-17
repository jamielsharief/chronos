<?php
declare(strict_types = 1);
namespace Chronos\Test\TestCase\Console\Command;

use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class ListCommandTest extends OriginTestCase
{
    protected $fixtures = ['Post'];

    use ConsoleIntegrationTestTrait;
    use BackupRestoreTrait;
    
    protected function setUp(): void
    {
        $this->beforeTest();
    }

    protected function tearDown(): void
    {
        $this->afterTest();
    }

    public function testNoBackups()
    {
        $this->exec('list');
        $this->assertExitSuccess();
        $this->assertOutputContains('<warning>no backups found</warning>');
    }

    public function testBackups()
    {
        $file = 'chronos-2021011518440000.sql.bz2';
        file_put_contents($this->backupDirectory . '/' . $file, '');
        $this->exec('list');
        $this->assertExitSuccess();
        $this->assertOutputContains($file);
    }
}
