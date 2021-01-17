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

namespace Chronos\Test\TestCase;

use Origin\Model\Model;
use Chronos\BackupRestore;
use InvalidArgumentException;
use Origin\TestSuite\OriginTestCase;
use Chronos\Exception\FileNotFoundException;
use Chronos\Exception\DirectoryDoesNotExistException;

class Post extends Model
{
}
final class ChronosTest extends OriginTestCase
{
    protected $fixtures = ['Post'];
    protected $database;

    protected function setUp(): void
    {
        $this->database = env('DB_DATABASE');
        $this->loadModel('Post', ['className' => Post::class]);
    }

    public function testGetterSetters()
    {
        $backupRestore = BackupRestore::get();

        $this->assertEquals('/tmp', $backupRestore->directory('/tmp'));
        $this->assertEquals('/tmp', $backupRestore->directory());
    }

    public function testBackup()
    {
        $backupRestore = BackupRestore::get();
        
        $name = 'test-1' . date('YmdHis');
        $output = $backupRestore->directory() . '/' . $name . '.sql';

        $this->assertNotEmpty($backupRestore->backup($this->database, ['name' => $name]));
        $this->assertFileExists($output);
        
        return $name . '.sql';
    }

    /**
     * @depends testBackup
     */
    public function testRestore(string $backupName)
    {
        $this->Post->deleteAll(['id >' => 0]);
        $this->assertEquals(0, $this->Post->count());
       
        $backupRestore = BackupRestore::get();
    
        $this->assertTrue($backupRestore->restore($backupName, $this->database));
        $this->assertEquals(3, $this->Post->count());
    }

    public function testBackupDeep()
    {
        $backupRestore = BackupRestore::get();
        
        $name = 'deep/test-1' . date('YmdHis');
        $output = $backupRestore->directory() . '/' . $name . '.sql';

        $this->assertNotEmpty($backupRestore->backup($this->database, ['name' => $name]));
        $this->assertFileExists($output);
        
        return $name . '.sql';
    }

    /**
     * @depends testBackup
     */
    public function testRestoreDeep(string $backupName)
    {
        $this->Post->deleteAll(['id >' => 0]);
        $this->assertEquals(0, $this->Post->count());
       
        $backupRestore = BackupRestore::get();
    
        $this->assertTrue($backupRestore->restore($backupName, $this->database));
        $this->assertEquals(3, $this->Post->count());
    }

    public function testBackupCompressedZip()
    {
        $backupRestore = BackupRestore::get();

        $name = 'test-2' . date('YmdHis');
        $output = $backupRestore->directory() . '/' . $name . '.sql.zip';
     
        $this->assertNotEmpty($backupRestore->backup($this->database, [
            'name' => $name,
            'compression' => 'zip'
        ]));
        $this->assertFileExists($output);
        
        return $name . '.sql.zip';
    }

    /**
     * @depends testBackupCompressedZip
     */
    public function testRestoreCompressedZip(string $backupName)
    {
        $this->Post->deleteAll(['id >' => 0]);
        $this->assertEquals(0, $this->Post->count());
       
        $backupRestore = BackupRestore::get();
 
        $this->assertTrue($backupRestore->restore($backupName, $this->database));
        $this->assertEquals(3, $this->Post->count());
    }

    public function testBackupCompressedBzip2()
    {
        $backupRestore = BackupRestore::get();
     
        $name = 'test-3' . date('YmdHis');
        $output = $backupRestore->directory() . '/' . $name . '.sql.bz2';

        $this->assertNotEmpty($backupRestore->backup($this->database, [
            'name' => $name,
            'compression' => 'bzip2'
        ]));
        $this->assertFileExists($output);
        
        return $name . '.sql.bz2';
    }

    /**
     * @depends testBackupCompressedBzip2
     */
    public function testRestoreCompressedBzip2(string $backupName)
    {
        $this->Post->deleteAll(['id >' => 0]);
        $this->assertEquals(0, $this->Post->count());
       
        $backupRestore = BackupRestore::get();
 
        $this->assertTrue($backupRestore->restore($backupName, $this->database));
        $this->assertEquals(3, $this->Post->count());
    }

    public function testBackupCompressedGzip()
    {
        $backupRestore = BackupRestore::get();

        $name = 'test-4' . date('YmdHis');
        $output = $backupRestore->directory() . '/' . $name . '.sql.gz';

        $this->assertNotEmpty($backupRestore->backup($this->database, [
            'name' => $name,
            'compression' => 'gzip'
        ]));
        $this->assertFileExists($output);
        
        return $name . '.sql.gz';
    }

    /**
     * @depends testBackupCompressedGzip
     */
    public function testRestoreCompressedGzip(string $backupName)
    {
        $this->Post->deleteAll(['id >' => 0]);
        $this->assertEquals(0, $this->Post->count());
       
        $backupRestore = BackupRestore::get();
 
        $this->assertTrue($backupRestore->restore($backupName, $this->database));
        $this->assertEquals(3, $this->Post->count());
    }

    public function testBackupCompressed7zip()
    {
        $backupRestore = BackupRestore::get();
      
        $name = 'test-5' . date('YmdHis');
        $output = $backupRestore->directory() . '/' . $name . '.sql.7z';

        $this->assertNotEmpty($backupRestore->backup($this->database, [
            'name' => $name,
            'compression' => '7zip'
        ]));
        $this->assertFileExists($output);
        
        return $name . '.sql.7z';
    }

    public function testWithGPGEncryptionBackup()
    {
        $backupRestore = BackupRestore::get();
     
        $name = 'test-6' . date('YmdHis');
        $output = $backupRestore->directory() . '/' . $name . '.sql.gpg';
        $this->assertNotEmpty($backupRestore->backup($this->database, [
            'name' => $name,
            'encryption' => 'gpg',
            'password' => 'secret'
        ]));
        $this->assertFileExists($output);

        return $name . '.sql.gpg';
    }

    /**
     * @depends testWithGPGEncryptionBackup
     */
    public function testWithGPGEncryptionRestore(string $backupName)
    {
        $this->Post->deleteAll(['id >' => 0]);
        $this->assertEquals(0, $this->Post->count());
       
        $backupRestore = BackupRestore::get();

        $this->assertTrue($backupRestore->restore($backupName, $this->database, ['password' => 'secret']));
        $this->assertEquals(3, $this->Post->count());
    }

    public function testWithSSLEncryptionBackup()
    {
        $backupRestore = BackupRestore::get();
     
        $name = 'test-6' . date('YmdHis');
        $output = $backupRestore->directory() . '/' . $name . '.sql.enc';
        $this->assertNotEmpty($backupRestore->backup($this->database, [
            'name' => $name,
            'encryption' => 'ssl',
            'password' => 'secret'
        ]));
        $this->assertFileExists($output);

        return $name . '.sql.enc';
    }

    /**
     * @depends testWithSSLEncryptionBackup
     */
    public function testWithSSLEncryptionRestore(string $backupName)
    {
        $this->Post->deleteAll(['id >' => 0]);
        $this->assertEquals(0, $this->Post->count());
       
        $backupRestore = BackupRestore::get();

        $this->assertTrue($backupRestore->restore($backupName, $this->database, ['password' => 'secret']));
        $this->assertEquals(3, $this->Post->count());
    }

    public function testBackupCompressedAndEncrypted()
    {
        $backupRestore = BackupRestore::get();

        $name = 'test-7' . date('YmdHis');
        $output = $backupRestore->directory() . '/' . $name . '.sql.gz.enc';
        $this->assertNotEmpty($backupRestore->backup($this->database, [
            'name' => $name,
            'compression' => 'gzip',
            'encryption' => 'ssl',
            'password' => 'secret'
        ]));
        $this->assertFileExists($output);

        return $name . '.sql.gz.enc';
    }

    /**
     * @depends testBackupCompressedAndEncrypted
     */
    public function testRestoreCompressedAndEncrypted(string $backupName)
    {
        $this->Post->deleteAll(['id >' => 0]);
        $this->assertEquals(0, $this->Post->count());
        
        $backupRestore = BackupRestore::get();

        $this->assertTrue($backupRestore->restore($backupName, $this->database, ['password' => 'secret']));
        $this->assertEquals(3, $this->Post->count());
    }

    public function testList()
    {
        $backupRestore = BackupRestore::get();
        $this->assertIsArray($backupRestore->list());
        $this->assertNotEmpty($backupRestore->list());
    }

    /**
     * @depends testList
     */
    public function testDelete()
    {
        $backupRestore = BackupRestore::get();
        $this->assertNotEmpty($backupRestore->list());

        foreach ($backupRestore->list() as $backup) {
            $this->assertTrue($backupRestore->delete($backup));
        }
           
        $this->assertEmpty($backupRestore->list());
    }

    public function testDeleteNotFound()
    {
        $backupRestore = BackupRestore::get();
        $this->expectException(FileNotFoundException::class);
        $backupRestore->delete(uniqid());
    }

    public function testInvalidDirectory()
    {
        $backupRestore = BackupRestore::get();
        $this->expectException(DirectoryDoesNotExistException::class);
        $backupRestore->directory('/foo');
    }

    public function testInvalidEncryptor()
    {
        $backupRestore = BackupRestore::get();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid encryption');
       
        $backupRestore->backup($this->database, [
            'name' => 'test-invalid-encryptor',
            'encryption' => 'pgp',
            'password' => 'foo'
        ]);
    }

    public function testInvalidCompressor()
    {
        $config = BackupRestore::config('default');
      
        BackupRestore::config('invalid-compressor', $config);
        $backupRestore = BackupRestore::get('invalid-compressor');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid compression');
       
        $backupRestore->backup($this->database, [
            'name' => 'test-invalid-compressor',
            'compression' => 'super-store'
        ]);
    }

    public function testUnconfigured()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid backup configuration');

        BackupRestore::get('does-not-exist');
    }

    public function testInvalidDatabaseEngine()
    {
        BackupRestore::config('foo', [
            'engine' => 'foo',
            'backupDirectory' => '/tmp'
        ]);
        $backupRestore = BackupRestore::get('foo');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid database engine');

        $backupRestore->backup('foo', ['name' => 'invalid-database-engine']);
    }
}
