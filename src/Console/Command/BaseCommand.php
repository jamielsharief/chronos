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
declare(strict_types = 1);
namespace Chronos\Console\Command;

use Exception;
use Chronos\BackupRestore;
use Chronos\Utility\Folder;
use Origin\ValueStore\ValueStore;
use Origin\Console\Command\Command;
use Origin\Model\ConnectionManager;

class BaseCommand extends Command
{
    /**
     * @var \Chronos\BackupRestore
     */
    protected $backupRestore;
    
    /**
     * @var \Origin\ValueStore\ValueStore
     */
    protected $config;

    /**
     * @return void
     */
    protected function startup(): void
    {
        $this->io->out('<yellow>' . $this->banner() . '</yellow>');
        $this->io->out('version <yellow>'  . $this->chronosVersion() . '</yellow>');
        $this->io->nl();
    }

    /**
     * @return string
     */
    private function chronosVersion(): string
    {
        $lines = file(ROOT . '/version.txt');

        return  $lines[0] ?? 'dev';
    }

    /**
     * @return string
     */
    protected function configurationPath(): string
    {
        return getcwd() . '/chronos.json';
    }

    /**
     * @return boolean
     */
    protected function isInitialized(): bool
    {
        return file_exists($this->configurationPath());
    }
   
    /**
     * Loads config file and sets up database connection, backup restore
     * and returns the list of available databases (checking that connection is possible)
     * @return array
     */
    protected function loadConfiguration(): array
    {
        $path = $this->configurationPath();

        if (! file_exists($path)) {
            $this->throwError('Chronos configuration not found', 'You must initialize chronos first.');
        }

        $this->setupChronos(new ValueStore($this->configurationPath()));

        return $this->databases();
    }

    /**
     * Sets up database connection, and configures backup restore engine
     *
     * @param \Origin\ValueStore\ValueStore$config $store
     * @return void
     */
    protected function setupChronos(ValueStore $store): void
    {
        $this->config = $store;
        ConnectionManager::config('default', $store->toArray());
        BackupRestore::config('default', $store->toArray());
        $this->backupRestore = BackupRestore::get('default');

        // Change to database directory for sqlite
        if ($store->databaseDirectory) {
            chdir($store->databaseDirectory);
        }
    }

    /**
    * @return array
    */
    protected function databases(): array
    {
        try {
            $connection = ConnectionManager::get('default');
        } catch (Exception $exception) {
            $this->throwError('Error connecting to database server', $exception->getMessage());
        }

        if ($connection->engine() !== 'sqlite') {
            return $connection->databases();
        }

        return (new Folder())->list($this->config->databaseDirectory);
    }

    /**
     * @return string
     */
    protected function banner(): string
    {
        return <<< EOT
                __                               
          _____/ /_  _________  ____  ____  _____
         / ___/ __ \/ ___/ __ \/ __ \/ __ \/ ___/
        / /__/ / / / /  / /_/ / / / / /_/ (__  ) 
        \___/_/ /_/_/   \____/_/ /_/\____/____/

      EOT;
    }
}
