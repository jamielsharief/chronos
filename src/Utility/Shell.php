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

use Chronos\Exception\CommandFailureException;

class Shell
{
    /**
     * @var array
     */
    private $output = [];

    /**
     * @var string
     */
    private $errorOutput = '';

    /**
     * Executes a command
     *
     * @param string $command
     * @return string
     */
    public function execute(string $command): string
    {
        $out = '';
        $commands = explode(' && ', $command);

        foreach ($commands as $cmd) {
            $out .= $this->exec($cmd);
        }

        return $out;
    }

    /**
     * @param string $command
     * @return string
     */
    private function exec(string $command): string
    {
        $this->output[] = $command . PHP_EOL;
        
        /**
         * WARNING: when using 2>&1 to redirect errors means warnings can end up
         * in dump files, so if dumps should not use >
         */
        $result = exec($command .' 2>&1', $output, $code);
        if ($result === false || $code !== 0) {
            throw new CommandFailureException($result);
        }
        $this->output[] = $this->convertOutput($output);

        return implode('', $this->output);
    }

    /**
    * Gets all output
    *
    * @return string
    */
    public function output(): string
    {
        return implode('', $this->output);
    }

    /**
     * Gets
     *
     * @return string
     */
    public function errors(): string
    {
        return $this->errorOutput;
    }

    /**
     * Resets the output vars
     *
     * @return void
     */
    public function reset(): void
    {
        $this->output = [];
        $this->errorOutput = '';
    }

    /**
     * @param mixed $output
     * @return string
     */
    private function convertOutput($output): string
    {
        return implode('', (array) $output);
    }
}
