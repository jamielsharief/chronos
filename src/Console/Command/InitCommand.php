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

use Chronos\Cmd\Database;
use Origin\ValueStore\ValueStore;

class InitCommand extends BaseCommand
{
    protected $name = 'init';
    protected $description = 'Initializes the installation';

    protected function execute(): void
    {
        if ($this->isInitialized()) {
            $this->throwError('Chronos has already been initialized');
        }

        $this->out('Starting chronos initialization');

        $engine = $this->io->askChoice('Engine', ['mysql','postgres','sqlite'], 'mysql');

        if (! in_array($engine, Database::available())) {
            $this->throwError('Invalid database engine');
        }

        $host = $port = $username = $password = $databaseDirectory = null;

        if (in_array($engine, ['mysql','postgres'])) {
            $host = $this->io->ask('Host', 'localhost');
            $port = $this->io->ask('Port', $engine === 'postgres' ? '5432' : '3306');
            $username = $this->io->ask('Username', $engine === 'postgres' ? 'postrgres' : 'root');
            $password = $this->io->askSecret('Password');
        } else {
            $databaseDirectory = $this->io->ask('Where are the Sqlite files stored');
        }

        $directory = $this->io->ask('Backup directory');
        if (! is_dir($directory) && ! mkdir($directory, 0775, true)) {
            $this->throwError('Error creating directory');
        }

        $store = new ValueStore($this->configurationPath());
        
        $store->host = $host;
        $store->port = $port;
        $store->username = $username;
        $store->password = $password;
        $store->engine = $engine;
        $store->backupDirectory = $directory;
        $store->databaseDirectory = $databaseDirectory;

        if (! $store->save()) {
            $this->throwError('Error saving configuration', 'Could not save to ' . $this->configurationPath());
        }
        $this->io->status('ok', 'chronos.json saved');
    }
}
