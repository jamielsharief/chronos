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

use Origin\Utility\Number;

class ListCommand extends BaseCommand
{
    protected $name = 'list';
    protected $description = 'Lists the backups';

    /**
     * @return void
     */
    protected function execute(): void
    {
        $this->loadConfiguration();
        
        $directory = $this->backupRestore->directory();
        $this->out('Backups path: <white>' . $directory. '</white>');

        $list = $this->backupRestore->list();

        if (! $list) {
            $this->out('<warning>no backups found</warning>');
            $this->exit();
        }
        rsort($list);

        $out = [
            ['Date', 'Backup','Size'] // Headers
        ];
        foreach ($list as $backup) {
            $path = $directory . '/' . $backup;
            $out[] = [date('Y-m-d H:i:s', filemtime($path)), $backup, Number::readableSize(filesize($path))];
        }
        $this->io->table($out);
    }
}
