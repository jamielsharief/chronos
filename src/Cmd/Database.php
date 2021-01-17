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

use InvalidArgumentException;
use Chronos\Cmd\Database\MySQL;
use Chronos\Cmd\Database\Sqlite;
use Chronos\Cmd\Database\Postgres;
use Chronos\Cmd\Database\BaseDatabase;

class Database extends BaseCommand
{
    /**
     * @var array
     */
    protected static $databaseMap = [
        'mysql' => MySQL::class,
        'postgres' => Postgres::class,
        'sqlite' => Sqlite::class
    ];
    
    /**
     * Gets a list of possible compressors
     *
     * @return array
     */
    public static function available(): array
    {
        return array_keys(static::$databaseMap);
    }

    /**
     * @param string $database
     * @param string $path
     * @param string $type
     * @return string
     */
    public function backup(string $database, string $path, string $type): string
    {
        $db = $this->load($type);
        $this->execute($db->backup($database, $path));

        return $path;
    }

    /**
     * @param string $path
     * @param string $database
     * @param string $type
     * @return string
     */
    public function restore(string $path, string $database, string $type): string
    {
        $db = $this->load($type);
        $this->execute($db->restore($path, $database));

        return $this->file->removeExtension($path, $db->extension());
    }

    /**
    * @param string $database
    * @return Basedatabase
    */
    public function load(string $database): ? BaseDatabase
    {
        if (! isset(static::$databaseMap[$database])) {
            throw new InvalidArgumentException('Invalid database engine');
        }
        $class = static::$databaseMap[$database];

        return new $class($this->config);
    }
}
