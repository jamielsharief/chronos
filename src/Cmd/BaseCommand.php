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
use Chronos\Utility\Shell;

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

    /**
     * @var \Chronos\Utility\Shell;
     */
    protected $shell;

    public function __construct(array $options = [])
    {
        $this->config = array_merge($this->defaultConfig, $options);

        $this->shell = new Shell();
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
        return $this->shell->execute($command);
    }
}
