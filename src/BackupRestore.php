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
namespace Chronos;

use RuntimeException;
use Chronos\Cmd\Database;
use Chronos\Utility\File;
use Chronos\Cmd\Encryptor;
use Chronos\Utility\Shell;
use BadMethodCallException;
use Chronos\Cmd\Compressor;
use InvalidArgumentException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Chronos\Exception\FileNotFoundException;
use Chronos\Exception\DirectoryDoesNotExistException;
use Origin\Configurable\StaticConfigurable as Configurable;

final class BackupRestore
{
    use Configurable;
    
    # # # SETTINGS # # #

    /**
     * Database type mysql, postgres, sqlite
     *
     * @var string
     */
    private $type;
    /**
     * Default output directory
     *
     * @var string
     */
    private $directory;

    # # # Objects # # #

    /**
     * @var \Chronos\Cmd\Database
     */
    private $database;

    /**
     * @var \Chronos\Cmd\Encryptor
     */
    private $encryptor;

    /**
     * @var \Chronos\Cmd\Compressor
     */
    private $compressor;

    /**
     * @var \Chronos\Utility\File
     */
    private $file;

    /**
     * @var \Chronos\Utility\Shell
     */
    private $shell;

    public function __construct(array $config = [])
    {
        $config += [
            'host' => 'localhost',
            'username' => null,
            'password' => null,
            'port' => null,
            'backupDirectory' => null, // backup files output,
            'databaseDirectory' => null,
            'engine' => null // mysql, sqlite, postgres
        ];

        if (empty($config['backupDirectory'])) {
            throw new InvalidArgumentException('You must provide a directory');
        }
        
        $this->type = $config['engine'];

        $this->file = new File();
        $this->shell = new Shell();

        $this->database = new Database($config);
        $this->compressor = new Compressor();
        $this->encryptor = new Encryptor();

        $this->directory($config['backupDirectory']);
    }

    /**
     * Sets gets the default output directory
     *
     * @param string $directory
     * @return string
     */
    public function directory(string $directory = null): string
    {
        if ($directory && ! is_dir($directory)) {
            throw new DirectoryDoesNotExistException($directory);
        }
      
        if (is_null($directory)) {
            $directory = $this->directory;
        }

        return $this->directory = $directory;
    }

    /**
     * Generates a name for a backup, e.g. application-20210113074748
     *
     * @param string $database
     * @param string $tag
     * @return string
     */
    private function generateName(string $database, string $tag = null): string
    {
        if (strpos($database, '/') !== false) {
            $database = pathinfo($database, PATHINFO_FILENAME);
        }

        if ($tag) {
            $tag .= '/';
        }
     
        return $tag . $database . '-' . date('YmdHis');
    }

    /**
     * @param string $database
     * @param string $name  e.g. application-20210113074009
     * @param array $options The following options are supported:
     *  - directory: output directory
     *  - compression: bzip2, gzip, 7zip and zip
     *  - encryption: aes
     *  - passphrase: if using encryption
     *  - tag: e.g. monthly, weekly, daily
     * @return string $path full path to backup
     */
    public function backup(string $database, array $options = []): string
    {
        $tag = $options['tag'] ?? null;

        $options += [
            'name' => $this->generateName($database, $tag),
            'directory' => $this->directory,
            'compression' => null,
            'encryption' => null,
            'password' => null
        ];

        // create deep directories e.g. monthly/bookmarks-20200110120000
        if (strpos($options['name'], '/') !== false) {
            $path = pathinfo($options['name'], PATHINFO_DIRNAME);
            @mkdir("{$options['directory']}/{$path}", 0755, true);
        }
       
        $out = $this->database->backup($database, "{$options['directory']}/{$options['name']}.sql", $this->type);

        if ($options['compression']) {
            $out = $this->compressor->compress($out, $options['compression']);
        }

        if ($options['encryption']) {
            if (empty($options['password'])) {
                throw new BadMethodCallException('Password not provided');
            }
            $out = $this->encryptor->encrypt($out, $options['password'], $options['encryption']);
        }

        return $out;
    }

    /**
     * Restores a backup
     *
     * @param string $path
     * @param string $database
     * @param array $options The following options are supported:
     *  - directory: output directory
     *  - passphrase: if using encryption
     * @return boolean
    */
    public function restore(string $path, string $database, array $options = []): bool
    {
        $options += [
            'directory' => $this->directory,
            'password' => null
        ];

        $path = $options['directory'] . '/' . $path;

        if (! file_exists($path)) {
            throw new InvalidArgumentException('Backup could not be found');
        }
  
        if ($this->file->isCompressed($path) || $this->file->isEncrypted($path)) {
            $path = $this->createTemporaryFile($path);
            
            // delete the temporary files and directory
            defer($matrix, function () use ($path) {
                $directory = pathinfo($path, PATHINFO_DIRNAME);
                \Chronos\Utility\Folder::delete($directory);
            });
        }
        
        if ($this->file->isEncrypted($path)) {
            if (empty($options['password'])) {
                throw new BadMethodCallException('Password not provided');
            }
            $path = $this->encryptor->decrypt($path, $options['password']);
        }

        if ($this->file->isCompressed($path)) {
            $path = $this->compressor->uncompress($path);
        }

        if ($this->file->isSql($path)) {
            $this->database->restore($path, $database, $this->type);

            return true;
        }
       
        return false;
    }

    /**
     * Creates a temporary copy of a backup file to use so that original backup
     * can be retained
     *
     * @param string $path
     * @return string
     */
    private function createTemporaryFile(string $path): string
    {
        $folder = sys_get_temp_dir() . '/chronos-' . uniqid();
        $backup = pathinfo($path, PATHINFO_BASENAME);

        if (! mkdir($folder) || ! copy($path, $folder . '/' . $backup)) {
            throw new RuntimeException('Error creating temporary file');
        }

        return $folder . '/' . $backup;
    }

    /**
     * Checks if a backup exists
     *
     * @param string $backup
     * @param array $options
     * @return boolean
     */
    public function exists(string $backup, array $options = []): bool
    {
        $options += [
            'directory' => $this->directory
        ];

        return is_file($options['directory'] . '/' . $backup);
    }

    /**
     * Lists the names of the backup
     *
     * @return array
     */
    public function list(): array
    {
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory));
        $start = strlen($this->directory) + 1;
        $out = [];
        foreach ($rii as $item) {
            if (! $item->isDir()) {
                $out[] = substr($item->getPathname(), $start);
            }
        }

        return $out;
    }

    /**
     * Deletes a backup, and empty directories
     *
     * @param string $name
     * @return boolean
     */
    public function delete(string $name): bool
    {
        $path = $this->directory . '/' . $name;
        if (file_exists($path)) {
            return unlink($path) && $this->deleteEmptyDirectory($name);
        }
        throw new FileNotFoundException('Backup not found');
    }

    /**
     * Checks if the name of backup is deep , e.g. monthly/mydb-20211012, if directory
     * is empty afterwards it will be removed
     *
     * @param string $name
     * @return boolean
     */
    private function deleteEmptyDirectory(string $name): bool
    {
        $path = $this->directory . '/' . $name;
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        
        if (strpos($name, '/') !== false && count(scandir($directory)) == 2) {
            return rmdir($directory);
        }

        return true;
    }

    /**
     * Gets a configured Chronos
     *
     * @param string $name
     * @return \Chronos\BackupRestore
     */
    public static function get(string $name = 'default'): BackupRestore
    {
        if (! static::config($name)) {
            throw new InvalidArgumentException('Invalid backup configuration');
        }
        $config = static::config($name);

        return new BackupRestore($config);
    }
    
    /**
     * Attribute accessor
     *
     * @param string $name
     * @param string $value
     * @return string|null
     */
    private function attribute(string $name, string $value = null)
    {
        if (is_null($value)) {
            $value = $this->$name;
        }

        return $this->$name = $value;
    }
}
