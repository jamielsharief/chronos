<?php
/**
 * Chronos Database Backup and Restore Database Backup & Restore
 * Copyright 2021 Jamiel Sharief.
 *
 * Licensed under The Apache License 2.0
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/Apache-2.0 Apache License 2.0
 */
declare(strict_types = 1);
namespace Chronos\Console\Command;

use Exception;
use Chronos\Utility\File;
use Origin\Utility\Number;
use Origin\Model\Connection;
use Origin\Model\ConnectionManager;
use Origin\Model\Exception\DatasourceException;

class RestoreCommand extends BaseCommand
{
    protected $name = 'restore';
    protected $description = 'Restores a backup';

    /**
     * @return void
     */
    protected function initialize(): void
    {
        $this->addArgument('backup', [
            'description' => 'Name of backup to restore',
            'type' => 'string',
            'required' => true
        ]);

        $this->addArgument('database', [
            'description' => 'Name of the database to restore to.',
            'type' => 'string'
        ]);
    }
 
    /**
     * @return void
     */
    protected function execute(): void
    {
        $databases = $this->loadConfiguration();
  
        $t = microtime(true);

        $backup = $this->requestedBackup(); // check first verify it exists
        $database = $this->requestedDatabase();

        $connection = $this->connectToDatabase($database);

        if (in_array($database, $databases)) {
            $this->overwriteDatabase($database);
        } else {
            $this->createDatabase($connection, $database);
        }

        $passphrase = $this->backupIsEncrypted($backup) ? $this->io->askSecret('Enter the password to decrypt') : null;

        try {
            $this->backupRestore->restore($backup, $database, [
                'password' => $passphrase
            ]);
        } catch (Exception $exception) {
            $this->throwError($exception->getMessage());
        }

        $this->io->status('ok', "Restore '{$database}' from '{$backup}'");
        $this->io->nl();
    
        $this->io->success(sprintf('Took %s seconds', Number::precision(microtime(true) - $t)));
    }

    /**
     * @param string $database
     * @return void
     */
    private function overwriteDatabase(string $database): void
    {
        $this->io->out("<exception> WARNING </exception> <yellow> Database '{$database}' already exists</yellow>");
        $answer = $this->io->askChoice('Existing data will be overwritten, continue?', ['yes','no'], 'no');
        if (! in_array($answer, ['yes','y'])) {
            $this->exit();
        }
        $this->io->status('skipped', "Create database '{$database}'");
    }

    /**
     * @param string $backup
     * @return boolean
     */
    private function backupIsEncrypted(string $backup): bool
    {
        return (new File())->isEncrypted($backup);
    }

    /**
     * @param string $database
     * @return \Origin\Model\Connection
     */
    private function connectToDatabase(string $database): Connection
    {
        try {
            $connection = ConnectionManager::get('default');
        } catch (Exception $exception) {
            $this->throwError('Error connecting to database server', $exception->getMessage());
        }

        return $connection;
    }

    /**
     * @param \Origin\Model\Connection $connection
     * @param string $database
     * @return void
     */
    private function createDatabase(Connection $connection, string $database): void
    {
        // Sqlite will create on connection
        if ($connection->engine() !== 'sqlite') {
            try {
                $connection->execute("CREATE DATABASE {$database}");
            } catch (DatasourceException $exception) {
                $this->throwError('DatasourceException', $exception->getMessage());
            }
        }
      
        $this->io->status('ok', "Create database '{$database}'");
    }

    /**
     * @return string
     */
    private function requestedBackup(): string
    {
        $backup = $this->arguments('backup');

        if (! $this->backupRestore->exists($backup)) {
            $this->throwError('Backup does not exist');
        }

        return $backup;
    }
    /**
     * Gets the database from the arguments or trys to detect from the backup name
     *
     * @return string
     */
    private function requestedDatabase(): string
    {
        if ($this->arguments('database')) {
            $database = $this->arguments('database');
        } else {
            $database = $this->detectDatabase($this->arguments('backup'));
        }

        return $database;
    }

    /**
     * @param string $backup
     * @return string
     */
    private function detectDatabase(string $backup): string
    {
        // work with monthly/db-name-20210115085539.sql
        if (strpos($backup, '/') !== false) {
            $backup = pathinfo($backup, PATHINFO_FILENAME);
        }

        if (! preg_match('/^(.*)-[\d]{14}.sql/', $backup, $matches)) {
            $this->throwError('Error detecting database name');
        }

        return $matches[1];
    }
}
