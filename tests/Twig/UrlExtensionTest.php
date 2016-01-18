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
 * Class AssetExtensionTest.
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class UrlExtensionTest extends \PHPUnit_Framework_TestCase
{
    use CommonTrait;

    /**
     * Test Twig url extension.
     *
     * @return void
     */
    public function testUrls() {
        $minion = $this->commonInitFramework(true);
        $request = Request::create('http://example.com/');
        $minion['request'] = $request;

        // url function
        /** @var \Twig_Function $urlFunction */
        $urlFunction = $minion['twig']->getFunction('url');
        $this->assertInstanceOf('\\Twig_Function', $urlFunction, 'Url Extension not found or misconfiguration occurred'
        );
        $urlCallable = $urlFunction->getCallable();
        $this->assertEquals("http://localhost/", $urlCallable('test'));

        // path function
        /** @var \Twig_Function $pathFunction */
        $pathFunction = $minion['twig']->getFunction('path');
        $this->assertInstanceOf('\\Twig_Function', $pathFunction, 'Url Extension not found or misconfiguration occurred'
        );
        $pathCallable = $pathFunction->getCallable();
        $this->assertEquals('/', $pathCallable('test'));
    }
}