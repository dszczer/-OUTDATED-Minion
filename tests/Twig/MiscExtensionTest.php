<?php

/**
 * This file is part of the Minion\Tests package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Tests\Twig;

use Minion\Tests\CommonTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MiscExtensionTest.
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class MiscExtensionTest extends \PHPUnit_Framework_TestCase
{
    use CommonTrait;

    /**
     * Test Twig misc extension.
     *
     * @return void
     */
    public function testMiscs() {
        $minion = $this->commonInitFramework(true);
        $request = Request::create('http://example.com/');
        $minion['request'] = $request;

        // static function
        $staticFunction = $minion['twig']->getFunction('static');
        $this->assertInstanceOf('\\Twig_Function', $staticFunction,
            'Misc Extension not found or misconfiguration occurred'
        );
        $staticCallable = $staticFunction->getCallable();
        // call date
        $this->assertEquals(\date('Y-m-d'), $staticCallable('date', ['Y-m-d']));
        // call static method
        /** @var \DateTime $dateTime */
        $dateTime = $staticCallable('\\DateTime::createFromFormat', [
                'Y-m-d',
                \date('Y-m-d'),
            ]
        );
        $this->assertInstanceOf('\\DateTime', $dateTime);
        $this->assertEquals(\date('Y-m-d'), $dateTime->format('Y-m-d'));
    }
}