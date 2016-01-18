<?php

/**
 * This file is part of the Minion\Tests package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Tests;

use Minion\Application;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * Class ConsoleTest.
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    use CommonTrait;

    /** @var ApplicationTester */
    private $console;

    /**
     * Fixture setup.
     *
     * @return void
     */
    public function setUp() {
        $this->console = $this->commonInitConsole();
    }

    /**
     * Test plain console environment run.
     *
     * @return void
     */
    public function testPlainRun() {
        $this->assertInstanceOf('Symfony\\Component\\Console\\Tester\\ApplicationTester', $this->console);

        $this->console->run([]);
        $output = $this->console->getDisplay();

        $this->assertContains('Minion\\Tests version ' . Application::VERSION, $output);
    }

    /**
     * Test command execution.
     *
     * @return void
     */
    public function testCommand() {
        $this->console->run(['test']);
        $output = $this->console->getDisplay();

        $this->assertEquals('test command succeeded', $output);
    }

    /**
     * Test integration with Propel commands.
     *
     * @return void
     */
    public function testPropelCommand() {
        $this->console->run(['propel:migration:status']);
        $output = $this->console->getDisplay();

        $this->assertContains('Checking Database Versions...', $output);
    }
}