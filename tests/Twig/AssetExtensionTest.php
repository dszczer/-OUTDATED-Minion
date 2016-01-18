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
use Minion\Utils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AssetExtensionTest.
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class AssetExtensionTest extends \PHPUnit_Framework_TestCase
{
    use CommonTrait;

    /**
     * Test Twig asset extension.
     *
     * @return void
     */
    public function testAssets() {
        $minion = $this->commonInitFramework(true);
        $request = Request::create('http://example.com/');
        $minion['request'] = $request;

        // asset function
        /** @var \Twig_Function $assetFunction */
        $assetFunction = $minion['twig']->getFunction('asset');
        $this->assertInstanceOf('\\Twig_Function', $assetFunction,
            'Assert Extension not found or misconfiguration occurred'
        );
        $assetCallable = $assetFunction->getCallable();
        $this->assertEquals('http://example.com/asset/test.ext', $assetCallable('asset/test.ext'));
        $this->assertEquals(Utils::fixPath($minion->getRootDir() . '/web/asset/test.ext'),
            $assetCallable('asset/test.ext', true)
        );
    }
}