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
namespace Chronos\Cmd\Database;

abstract class BaseDatabase
{
    /**
     * @var array
     */
    protected $defaultConfig = [];
    
    /**
     * @var array
     */
    protected $config = [];
    
    public function __construct(array $options = [])
    {
        $this->config = array_merge($this->defaultConfig, $options);
    }

    abstract public function backup(string $database, string $path): string;
    abstract public function restore(string $path, string $database): string;

    /**
     * @return string
     */
    public function extension(): string
    {
        return 'sql';
    }
}
