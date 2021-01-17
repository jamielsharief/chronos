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

class File
{

    /**
     * @param string $path
     * @return string
     */
    public function extension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * Removes an extension from a path
     *
     * @param string $path
     * @param string $extension
     * @return string
     */
    public function removeExtension(string $path, string $extension): string
    {
        $length = strlen($extension);

        return substr($path, -$length) === $extension ? substr($path, 0, -($length + 1)) : $path;
    }

    /**
     * @param string $path
     * @return boolean
     */
    public function isCompressed(string $path): bool
    {
        return in_array($this->extension($path), ['7z','bz2','gz','rar','tar','zip']);
    }

    /**
     * @param string $path
     * @return boolean
     */
    public function isEncrypted(string $path)
    {
        return in_array($this->extension($path), ['enc','gpg']);
    }

    /**
     * @param string $path
     * @return boolean
     */
    public function isSql(string $path): bool
    {
        return $this->extension($path) === 'sql';
    }
}
