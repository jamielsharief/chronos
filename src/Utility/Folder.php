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
namespace Chronos\Utility;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Folder
{
    /**
     * Lists files in a directory
     *
     * @return array
     */
    public static function list(string $directory): array
    {
        $start = strlen($directory) + 1;
        $out = [];

        $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($items  as $item) {
            if (! $item->isDir()) {
                $out[] = substr($item->getPathname(), $start);
            }
        }

        return $out;
    }

    /**
     * Deletes a folder and all items in the folder
     *
     * @param string $directory
     * @return boolean
     */
    public static function delete(string $directory): bool
    {
        if (! file_exists($directory) || ! is_dir($directory)) {
            return false;
        }
    
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
    
        foreach ($items  as $item) {
            $action = $item->isDir() ? 'rmdir' : 'unlink';
            if (! @$action($item->getRealPath())) {
                return false;
            }
        }
    
        return rmdir($directory);
    }
}
