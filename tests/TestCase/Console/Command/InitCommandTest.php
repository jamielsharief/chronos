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
        $this->exec('init', [
            'mysql',
            'localhost',
            '3306',
            'root',
            'root',
            '/backup'
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
            "backupDirectory": "/backup",
            "databaseDirectory": null
        }
        EOT;

        $this->assertEquals($expected, file_get_contents($this->configPath()));
        unlink($this->configPath());
    }

    public function testInitSqlite()
    {
        $this->exec('init', [
            'sqlite',
            '/data',
            '/backup'
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
            "backupDirectory": "/backup",
            "databaseDirectory": "/data"
        }
        EOT;

        $this->assertEquals($expected, file_get_contents($this->configPath()));
        unlink($this->configPath());
    }

    public function testInitException()
    {
        file_put_contents($this->configPath(), json_encode([
            'engine' => 'sqlite',
            'backupDirectory' => '/backup',
            'databaseDirectory' => '/data'
        ]));

        $this->exec('init');
        $this->assertExitError();
        $this->assertErrorContains('Chronos has already been initialized');

        unlink($this->configPath());
    }
}
