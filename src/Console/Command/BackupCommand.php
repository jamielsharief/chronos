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
use Origin\Utility\Number;

class BackupCommand extends BaseCommand
{
    protected $name = 'backup';
    protected $description = 'Backup a database';

    /**
     * @return void
     */
    protected function initialize(): void
    {
        $this->addArgument('database', [
            'description' => 'Name of the database(s) to restore to.',
            'type' => 'array'
        ]);

        $this->addOption('compress', [
            'description' => 'Compression to use. e.g. 7zip, bzip2, gzip and zip',
            'type' => 'string'
        ]);

        $this->addOption('encrypt', [
            'description' => 'Encryption to use. e.g gpg, openssl',
            'type' => 'string'
        ]);

        $this->addOption('password', [
            'description' => 'The passphrase to use with encryption',
            'type' => 'string'
        ]);
    }

    /**
     * @return void
     */
    protected function execute(): void
    {
        $databases = $this->loadConfiguration();
   
        if ($this->options('encrypt')) {
            $this->options['password'] = $this->password();
        }

        $wants = $this->arguments('database') ?: (array) $this->prompt($databases);

        $t = microtime(true);
        foreach ($wants as $database) {
            if (! in_array($database, $databases)) {
                $this->io->status('error', "Backup '{$database}'");
                $this->debug('database does not exist');
                continue;
            }
            $backupFile = $this->backupDatabase($database);
            $this->io->status('ok', "Backup '{$database}'");
            $this->debug($backupFile);
        }
   
        $this->io->nl();
        $this->io->success(sprintf('Took %s seconds', Number::precision(microtime(true) - $t)));
    }

    /**
     * @param array $databases
     * @return string
     */
    private function prompt(array $databases): string
    {
        $this->out('Databases found:');
        $this->io->nl();

        foreach ($databases as $database) {
            $this->io->list($database, '-');
        }

        $this->io->nl();
        $wants = $this->io->ask('Which database?');

        return in_array($wants, $databases) ? $wants : $this->prompt($database);
    }

    /**
     * @return string
     */
    private function password(): string
    {
        $password = env('CHRONOS_PASSWORD') ;

        if (! $password) {
            $password = $this->io->askSecret('Enter a password to encrypt the backup with');
        }

        return $password;
    }

    /**
     * @param string $database
     * @return string
     */
    private function backupDatabase(string $database): string
    {
        try {
            $output = $this->backupRestore->backup($database, $this->backupOptions());
        } catch (Exception $exception) {
            $this->throwError($exception->getMessage());
        }

        return $output;
    }

    /**
     * @return array
     */
    private function backupOptions(): array
    {
        return [
            'compression' => $this->options('compress'),
            'encryption' => $this->options('encrypt'),
            'password' => $this->options('password')
        ];
    }
}
