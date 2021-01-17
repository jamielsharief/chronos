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

namespace Chronos\Test\TestCase;

use Chronos\Utility\Shell;
use Origin\TestSuite\OriginTestCase;
use Chronos\Exception\CommandFailureException;

final class ShellTest extends OriginTestCase
{
    public function testExecuteSingleCommand()
    {
        $shell = new Shell();
        $shell->execute('php --version');
        $this->assertStringContainsString('Zend Technologies', $shell->output());
    }

    public function testExecuteMultipleCommands()
    {
        $shell = new Shell();
        $shell->execute('php --version && ls /etc');
        $this->assertStringContainsString('Zend Technologies', $shell->output());
        $this->assertStringContainsString('sysctl.d', $shell->output());
    }

    public function testExecuteSingleCommandError()
    {
        $this->expectException(CommandFailureException::class);
        $this->expectExceptionMessage("ls: cannot access '/boo': No such file or directory");
        (new Shell())->execute('ls -la /boo');
    }

    public function testExecuteMultipleCommandsError()
    {
        $this->expectException(CommandFailureException::class);
        $this->expectExceptionMessage("ls: cannot access '/boo': No such file or directory");
        (new Shell())->execute('php --version && ls -la /boo');
    }

    public function testReset()
    {
        $shell = new Shell();
        $shell->execute('php --version');
        $this->assertStringContainsString('Zend Technologies', $shell->output());
        $shell->reset();
        $this->assertEmpty($shell->output());
        $this->assertEmpty($shell->errors());
    }
}
