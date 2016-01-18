<?php

/**
 * This file is part of the Minion\Tests package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Tests;

use Minion\Controller;
use Minion\Tests\Controller\TestController;

/**
 * Class ControllerTest.
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
{
    use CommonTrait;

    /** @var Controller */
    private $controller;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
        parent::setUp();

        // here TestController because of not being able to instantiate abstract class Minion\Controller
        $this->controller = new TestController();
        $minion = $this->commonInitFramework();

        $this->controller->setContainer($minion);
    }

    /**
     * Test render template methods (with and without Response).
     *
     * @return void
     */
    public function testRenderTemplates() {
        $content = $this->controller->renderText('test.html.twig', ['arg' => '']);
        $this->assertContains('Page loaded successfully', $content);

        $response = $this->controller->render('test.html.twig', ['arg' => '']);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertContains('Page loaded successfully', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test creating error responses.
     *
     * @return void
     */
    public function testErrorResponse() {
        // 404 error response
        $response = $this->controller->createNotFoundException('Not found response');
        $this->assertInstanceOf('\\Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertContains('Not found response', $response->getContent());
        $this->assertEquals(404, $response->getStatusCode());

        // 403 error response
        $response = $this->controller->createNotAllowedException('Forbidden response');
        $this->assertInstanceOf('\\Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertContains('Forbidden response', $response->getContent());
        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test getParameter helper method.
     *
     * @return void
     */
    public function testParameterHelper() {
        // parameter doesn't exist, return default
        $value = $this->controller->getParameter('non-existing', false);
        $this->assertEquals(false, $value);

        // parameter exist, return value
        $value = $this->controller->getParameter('exist');
        $this->assertEquals(true, $value);
    }

    /**
     * Test generateUrl helper method.
     *
     * @return void
     */
    public function testGenerateUrlHelper() {
        // url for "homepage"
        $url = $this->controller->generateUrl('test');
        $this->assertEquals('http://localhost/', $url);
    }
}