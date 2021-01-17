<?php
declare(strict_types = 1);
namespace Chronos\Test\TestCase\Console\Command;

use Chronos\BackupRestore;
use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class RestoreCommandTest extends OriginTestCase
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
   
    public function testBackupAndRestore()
    {
        $database = env('DB_DATABASE');
        $this->exec('backup ' . $database . ' -v');
        $this->assertExitSuccess();
        $backups = BackupRestore::get()->list();

        $this->exec('restore ' . $backups[0] . ' -v', ['yes']);
        $this->assertExitSuccess();
        $this->assertOutputContains('Existing data will be overwritten, continue?  (yes/no)');

        // Sqlite data/foo.db shows as foo. so not testing now
        $this->assertOutputContains('<cyan>SKIPPED</cyan> <white>] Create database');
        $this->assertOutputContains('green>OK</green> <white>] Restore');
    }

    public function testBackupAndRestoreToDifferentDb()
    {
        $randomdb = 'restore_test_' . time();

        $database = env('DB_DATABASE');
        $this->exec('backup ' . $database . ' -v');
        $this->assertExitSuccess();
        $backups = BackupRestore::get()->list();

        $this->exec('restore ' . $backups[0] . ' ' . $randomdb . ' -v');

        $this->assertExitSuccess();
        $this->assertOutputNotContains('Existing data will be overwritten, continue?  (yes/no)');

        // Sqlite data/foo.db shows as foo. so not testing now
        $this->assertOutputContains('<green>OK</green> <white>] Create database');
        $this->assertOutputContains('green>OK</green> <white>] Restore');
    }

    public function testBackupAndRestoreCompression()
    {
        $database = env('DB_DATABASE');
        $this->exec('backup ' . $database . ' --compress bzip2 -v');
        $this->assertExitSuccess();
        $backups = BackupRestore::get()->list();

        $this->exec('restore ' . $backups[0] . ' -v', ['yes']);
        $this->assertExitSuccess();
        $this->assertOutputContains('Existing data will be overwritten, continue?  (yes/no)');

        $this->assertOutputContains('<cyan>SKIPPED</cyan> <white>] Create database');
        $this->assertOutputContains('green>OK</green> <white>] Restore');
    }

    public function testBackupAndRestoreEncryption()
    {
        $database = env('DB_DATABASE');
        $this->exec('backup ' . $database . ' --compress bzip2 --encrypt gpg -v', ['secret']);
        $this->assertExitSuccess();
        $backups = BackupRestore::get()->list();

        $this->exec('restore ' . $backups[0] . ' -v', ['yes','secret']);
        $this->assertExitSuccess();

        $this->assertOutputContains('<cyan>SKIPPED</cyan> <white>] Create database');
        $this->assertOutputContains('green>OK</green> <white>] Restore');
    }
}
