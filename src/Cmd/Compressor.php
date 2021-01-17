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
use Chronos\Cmd\Compression\Zip;
use Chronos\Cmd\Compression\Gzip;
use Chronos\Cmd\Compression\Bzip2;
use Chronos\Cmd\Compression\SevenZip;
use Chronos\Exception\ChronosException;
use Chronos\Cmd\Compression\BaseCompression;

class Compressor extends BaseCommand
{

    /**
     * @var array
     */
    protected static $compressionMap = [
        'zip' => Zip::class,
        'gzip' => Gzip::class,
        '7zip' => SevenZip::class,
        'bzip2' => Bzip2::class
    ];

    /**
     * @var array
     */
    protected $extensionMap = [
        'zip' => 'zip',
        'gz' => 'gzip',
        'bz2' => 'bzip2',
        '7z' => '7zip'
    ];

    /**
     * Gets a list of possible compressors
     *
     * @return array
     */
    public static function available(): array
    {
        return array_keys(static::$compressionMap);
    }

    /**
     * @param string $path
     * @param string $compression
     * @return string
     */
    public function compress(string $path, string $compression): string
    {
        $compressor = $this->load($compression);
        $this->execute($compressor->compress($path));

        return $path .  '.' .  $compressor->extension();
    }

    /**
    * @param string $path
    * @return string
    */
    public function uncompress(string $path): string
    {
        $compressor = $this->load($this->detectCompressionType($path));
        $this->execute($compressor->uncompress($path));

        return $this->file->removeExtension($path, $compressor->extension());
    }

    /**
     * @param string $path
     * @return string
     */
    private function detectCompressionType(string $path): string
    {
        $extension = $this->file->extension($path);

        if (! isset($this->extensionMap[$extension])) {
            throw new ChronosException('Unrecognsied file type');
        }

        return $this->extensionMap[$extension];
    }

    /**
     * @param string $compression
     * @return BaseCompression
     */
    private function load(string $compression): BaseCompression
    {
        if (! isset(static::$compressionMap[$compression])) {
            throw new InvalidArgumentException('Invalid compression');
        }
        $class = static::$compressionMap[$compression];

        return new $class();
    }
}
