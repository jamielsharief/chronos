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
use Chronos\Cmd\Encryption\Gpg;
use Chronos\Cmd\Encryption\OpenSSL;
use Chronos\Exception\ChronosException;
use Chronos\Cmd\Encryption\BaseEncryption;
use Chronos\Exception\CommandFailureException;

class Encryptor extends BaseCommand
{
    protected static $encryptionMap = [
        'ssl' => OpenSSL::class,
        'gpg' => Gpg::class
    ];

    protected $extensionMap = [
        'enc' => 'ssl',
        'gpg' => 'gpg'
    ];

    /**
     * Gets a list of possible compressors
     *
     * @return array
     */
    public static function available(): array
    {
        return array_keys(static::$encryptionMap);
    }

    /**
     * @param string $path
     * @param string $passphrase
     * @param string $encryption
     * @return string
     */
    public function encrypt(string $path, string $passphrase, string $encryption): string
    {
        $encryptor = $this->load($encryption);
        $this->execute($encryptor->encrypt($path, $passphrase));

        return $path .  '.' .  $encryptor->extension();
    }

    /**
     * @param string $path
     * @param string $passphrase
     * @return string
     */
    public function decrypt(string $path, string $passphrase): string
    {
        $encryptor = $this->load($this->detectEncryptionType($path));

        try {
            $this->execute($encryptor->decrypt($path, $passphrase));
        } catch (CommandFailureException $exception) {
            /**
             * - Error with passphrase (highly likely)
             * - Different versions of openssl e.g. libressl
             * - Possible different configuration e.g. openssl 1-1.1 changd default hash md5, sha256
             * - Other open SSL configuration, e.g user disabled padding which enabled by default. e.g EVP_CIPHER_CTX_set_padding(context, 0);
             */
            throw new CommandFailureException('Error decrypting file, please check passphrase');
        }
  
        return $this->file->removeExtension($path, $encryptor->extension());
    }

    /**
     * @param string $path
     * @return string
     */
    private function detectEncryptionType(string $path): string
    {
        $extension = $this->file->extension($path);

        if (! isset($this->extensionMap[$extension])) {
            throw new ChronosException('Unrecognsied file type');
        }

        return $this->extensionMap[$extension];
    }

    /**
      * @param string $encryption
      * @return \Chronos\Cmd\Encryption\BaseEncryption
      */
    private function load(string $encryption): BaseEncryption
    {
        if (! isset(static::$encryptionMap[$encryption])) {
            throw new InvalidArgumentException('Invalid encryption');
        }
        $class = static::$encryptionMap[$encryption];

        return new $class();
    }
}
