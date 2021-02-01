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
namespace Chronos\Cmd;

use Chronos\Utility\File;
use Origin\Process\Process;
use Chronos\Exception\CommandFailureException;

class BaseCommand
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $defaultConfig = [];

    /**
     * @var \Chronos\Utility\File
     */
    protected $file;

    public function __construct(array $options = [])
    {
        $this->config = array_merge($this->defaultConfig, $options);
        $this->file = new File();
    }

    /**
     * Executes a command in the shell
     *
     * @param string $command
     * @return string
     */
    protected function execute(string $command): string
    {
        $process = new Process($command, ['escape' => false,'output' => false]);
        if (! $process->execute()) {
            throw new CommandFailureException($process->error() ?: 'Error running: ' . $command);
        }

        return $process->output();
    }
}
