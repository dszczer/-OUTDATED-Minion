<?php

/**
 * This file is part of the Minion\Tests package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Tests;

use Minion\Utils;

/**
 * Class UtilsTest.
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check if template exists, then if not exists.
     *
     * @return void
     */
    public function testToolTemplateExists() {
        $loader = new \Twig_Loader_Filesystem(\realpath(__DIR__ . '/src/Resources/views'));
        $twig = new \Twig_Environment($loader);

        $this->assertTrue(Utils::templateExists($twig, 'test.html.twig'));
        $this->assertNotTrue(Utils::templateExists($twig, 'non-existing.html.twig'));
    }

    /**
     * Check if fixing paths works correctly.
     *
     * @return void
     */
    public function testToolFixPath() {
        // test for invalid directory separators
        $this->assertEquals('foo' . DIRECTORY_SEPARATOR . 'bar.ext', Utils::fixPath('foo\\/bar.ext'));

        // test for multiple directory separators
        $this->assertEquals('foo' . DIRECTORY_SEPARATOR . 'ups' . DIRECTORY_SEPARATOR . 'bar.ext',
            Utils::fixPath('foo\\\\\\\\ups/////bar.ext')
        );
    }
}