<?php
declare(strict_types = 1);
namespace Chronos\Test\TestCase\Console\Command;

use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class InitCommandTest extends OriginTestCase
{
    use ConsoleIntegrationTestTrait;
    
    protected function configPath()
    {
        return getcwd() . '/chronos.json';
    }

    public function testInitMySQL()
    {
        $backupFolder = sys_get_temp_dir() . '/' . uniqid();

        $this->exec('init', [
            'mysql',
            'localhost',
            '3306',
            'root',
            'root',
            $backupFolder,
            ''
        ]);
        $this->assertExitSuccess();
        $this->assertOutputContains('chronos.json saved');

        $expected = <<< EOT
        {
            "host": "localhost",
            "port": "3306",
            "username": "root",
            "password": "root",
            "engine": "mysql",
            "backupDirectory": "{$backupFolder}",
            "databaseDirectory": null,
            "databases": null
        }
        EOT;

        $this->assertEquals($expected, file_get_contents($this->configPath()));
        unlink($this->configPath());
    }

    public function testInitWithDatabases()
    {
        $backupFolder = sys_get_temp_dir() . '/' . uniqid();

        $this->exec('init', [
            'mysql',
            'localhost',
            '3306',
            'root',
            'root',
            $backupFolder,
            'foo'
        ]);
        $this->assertExitSuccess();
        $this->assertOutputContains('chronos.json saved');

        $expected = <<< EOT
        {
            "host": "localhost",
            "port": "3306",
            "username": "root",
            "password": "root",
            "engine": "mysql",
            "backupDirectory": "{$backupFolder}",
            "databaseDirectory": null,
            "databases": [
                "foo"
            ]
        }
        EOT;
        
        $this->assertEquals($expected, file_get_contents($this->configPath()));
        unlink($this->configPath());
    }

    public function testInitSqlite()
    {
        $backupFolder = sys_get_temp_dir() . '/' . uniqid();
        
        $this->exec('init', [
            'sqlite',
            '/data',
            $backupFolder,
            ''
        ]);
        $this->assertExitSuccess();
        $this->assertOutputContains('chronos.json saved');

        $expected = <<< EOT
        {
            "host": null,
            "port": null,
            "username": null,
            "password": null,
            "engine": "sqlite",
            "backupDirectory": "{$backupFolder}",
            "databaseDirectory": "/data",
            "databases": null
        }
        EOT;

        $this->assertEquals($expected, file_get_contents($this->configPath()));
        unlink($this->configPath());
    }

    public function testInitException()
    {
        $backupFolder = sys_get_temp_dir() . '/' . uniqid();

        file_put_contents($this->configPath(), json_encode([
            'engine' => 'sqlite',
            'backupDirectory' => $backupFolder,
            'databaseDirectory' => '/data'
        ]));

        $this->exec('init');
        $this->assertExitError();
        $this->assertErrorContains('Chronos has already been initialized');

        unlink($this->configPath());
    }
}
