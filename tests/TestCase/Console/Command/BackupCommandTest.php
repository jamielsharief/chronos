<?php
declare(strict_types = 1);
namespace Chronos\Test\TestCase\Console\Command;

use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class BackupCommandTest extends OriginTestCase
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

    public function testBackup()
    {
        $database = env('DB_DATABASE');
        $this->exec('backup ' . $database. ' -v');

        $this->assertExitSuccess();
        $this->assertOutputContains("<white>[</white> <green>OK</green> <white>] Backup '{$database}'</white>");
        $this->assertOutputRegExp('/chronos_test(.db)?-[\d]{14}.sql/');
    }

    public function testBackupNoDatabase()
    {
        $database = env('DB_DATABASE');
        $this->exec('backup', [$database]);
        $this->assertOutputContains('Databases found:');
        $this->assertOutputContains($database);
    }

    public function testBackupUnkownDatabse()
    {
        $this->exec('backup xyz -v');

        $this->assertExitSuccess();
        $this->assertOutputContains("<red>ERROR</red> <white>] Backup 'xyz'</white>");
    }

    public function testBackupWithCompression()
    {
        $database = env('DB_DATABASE');
        $this->exec('backup ' . $database . ' --compress zip -v');

        $this->assertExitSuccess();
        $this->assertOutputContains("<white>[</white> <green>OK</green> <white>] Backup '{$database}'</white>");
        $this->assertOutputRegExp('/chronos_test(.db)?-[\d]{14}.sql.zip/');
    }

    public function testBackupWithEncryption()
    {
        $database = env('DB_DATABASE');
        $this->exec('backup ' . $database . ' --encrypt gpg -v', ['secret']);

        $this->assertExitSuccess();
        $this->assertOutputContains("<white>[</white> <green>OK</green> <white>] Backup '{$database}'</white>");
        $this->assertOutputRegExp('/chronos_test(.db)?-[\d]{14}.sql.gpg/');
    }

    public function testBackupWithEncryptionDontPrompt()
    {
        $_ENV['CHRONOS_PASSWORD'] = 'secret';

        $database = env('DB_DATABASE');
        $this->exec('backup ' . $database . ' --encrypt gpg -v', );

        unset($_ENV['CHRONOS_PASSWORD']);
        $this->assertExitSuccess();
        $this->assertOutputContains("<white>[</white> <green>OK</green> <white>] Backup '{$database}'</white>");
        $this->assertOutputRegExp('/chronos_test(.db)?-[\d]{14}.sql.gpg/');
    }

    public function testBackupWithCompressionAndEncryption()
    {
        $database = env('DB_DATABASE');
        $this->exec('backup ' . $database . ' --compress bzip2 --encrypt gpg -v', ['secret']);

        $this->assertExitSuccess();
        $this->assertOutputContains("<white>[</white> <green>OK</green> <white>] Backup '{$database}'</white>");
        $this->assertOutputRegExp('/chronos_test(.db)?-[\d]{14}.sql.bz2.gpg/');
    }
}
