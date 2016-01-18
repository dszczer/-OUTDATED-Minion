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
use Propel\Runtime\Propel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class ApplicationTest.
 *
 * Wrapper test cases.
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    use CommonTrait;

    private $storedErrorPages = [];

    /**
     * Execute one simple database query to test connection.
     *
     * @return void
     */
    public function testDatabaseConnection() {
        $minion = $this->commonInitFramework();
        // must boot services to init propel connection
        $minion->boot();

        $con = Propel::getConnection();
        $date = $con->query("SELECT date('now') AS `date`")->fetch();

        $this->assertArrayHasKey(0, $date);
        $this->assertEquals(\date('Y-m-d'), $date[0]);
    }

    /**
     * Initialize framework and check it's condition.
     *
     * @return void
     */
    public function testPlainRun() {
        $minion = $this->commonInitFramework();
        $this->assertInstanceOf('\\Minion\\Application', $minion);

        $this->assertArrayHasKey('debug', $minion);
        $this->assertInternalType('bool', $minion['debug']);

        $this->assertArrayHasKey('twig', $minion);
        $this->assertInstanceOf('\\Twig_Environment', $minion['twig']);

        $this->assertArrayHasKey('monolog', $minion);
        $this->assertInstanceOf('\\Monolog\\Logger', $minion['monolog']);

        $this->assertArrayHasKey('console', $minion);
        $this->assertInstanceOf('\\Symfony\\Component\\Console\\Application', $minion['console']);

        $this->assertArrayHasKey('routes', $minion);
        $this->assertInstanceOf('\\Symfony\\Component\\Routing\\RouteCollection', $minion['routes']);

        $this->assertArrayHasKey('url_generator', $minion);
        $this->assertInstanceOf('\\Symfony\\Component\\Routing\\Generator\\UrlGenerator', $minion['url_generator']);

        $this->assertArrayHasKey('parameters', $minion);
        $this->assertInstanceOf('\\Symfony\\Component\\HttpFoundation\\ParameterBag', $minion['parameters']);

        $this->assertEquals(true, $minion['twig']->hasExtension('twig_tag_test_extension'));
    }

    /**
     * Test getter methods.
     *
     * @return void
     */
    public function testGetters() {
        $minion = $this->commonInitFramework();

        $this->assertEquals($this->fixedRootDir, $minion->getRootDir());
        $this->assertEquals($this->fixedPackageDir, $minion->getPackageDir());
        $this->assertEquals('Minion\\Tests', $minion->getAppNamespace());
        $this->assertEquals(DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR, $minion->getConfigPath());
        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'propel.php', $minion->getPropelConfigPath());
    }

    /**
     * Load simple page, with templating.
     *
     * @return void
     */
    public function testSimplePage() {
        $minion = $this->commonInitFramework();

        $request = Request::create('/');
        $response = $this->commonRunRaw($minion, $request);

        $this->assertContains('Page loaded successfully', $response->getContent());
        $this->assertContains('"sample argument"', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test custom error pages.
     *
     * @return void
     */
    public function testCustomErrorPages() {
        // 403
        $minion = $this->commonInitFramework(false);
        $request = Request::create('/error/403');
        $response = $minion->handle($request, HttpKernelInterface::MASTER_REQUEST, true);
        $minion->terminate($request, $response);
        $this->assertEquals('Error 403 template', $response->getContent());
        $this->assertEquals(403, $response->getStatusCode());

        // 404
        $minion = $this->commonInitFramework(false);
        $request = Request::create('/non/existing/path');
        $response = $minion->handle($request, HttpKernelInterface::MASTER_REQUEST, true);
        $minion->terminate($request, $response);
        $this->assertEquals('Error 404 template', $response->getContent());
        $this->assertEquals(404, $response->getStatusCode());

        // 500
        $minion = $this->commonInitFramework(false);
        $request = Request::create('/error/500');
        $response = $minion->handle($request, HttpKernelInterface::MASTER_REQUEST, true);
        $minion->terminate($request, $response);
        $this->assertEquals('Error 500 template', $response->getContent());
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Test default (blank) error pages.
     *
     * @return void
     */
    public function testBlankErrorPages() {
        // 403
        try {
            $this->fixtureDeleteErrorPageFile(403);
            $minion = $this->commonInitFramework(false);
            $request = Request::create('/error/403');
            $response = $minion->handle($request, HttpKernelInterface::MASTER_REQUEST, true);
            $minion->terminate($request, $response);
            $this->assertContains('Error 403 occured', $response->getContent());
            $this->assertEquals(403, $response->getStatusCode());
        } finally {
            $this->fixtureRestoreErrorPageFile(403);
        }

        // 404
        try {
            $this->fixtureDeleteErrorPageFile(404);
            $minion = $this->commonInitFramework(false);
            $request = Request::create('/non/existing/path');
            $response = $minion->handle($request, HttpKernelInterface::MASTER_REQUEST, true);
            $minion->terminate($request, $response);
            $this->assertContains('Error 404 occured', $response->getContent());
            $this->assertEquals(404, $response->getStatusCode());
        } finally {
            $this->fixtureRestoreErrorPageFile(404);
        }

        // 500
        try {
            $this->fixtureDeleteErrorPageFile(500);
            $minion = $this->commonInitFramework(false);
            $request = Request::create('/error/500');
            $response = $minion->handle($request, HttpKernelInterface::MASTER_REQUEST, true);
            $minion->terminate($request, $response);
            $this->assertContains('Error 500 occured', $response->getContent());
            $this->assertEquals(500, $response->getStatusCode());
        } finally {
            $this->fixtureRestoreErrorPageFile(500);
        }
    }

    /**
     * Test custom service providing.
     *
     * @return void
     */
    public function testCustomService() {
        $minion = $this->commonInitFramework(false);
        $service = $minion['test_service'];
        $this->assertInstanceOf('\\Minion\\Tests\\Service\\Service', $service);

        $service->setOption($minion['test_service.option']);
        $this->assertEquals('hello world', $service->testMe());
    }

    /**
     * Test namespace detection.
     *
     * @return void
     */
    public function testNamespaceDetection() {
        $this->commonSetPaths();
        $minion = new Application('', [
            'debug' => true,
            'environment' => 'test'
        ], [
                'rootDir' => $this->fixedRootDir,
                'packageDir' => $this->fixedPackageDir
            ]
        );

        $this->assertEquals('Minion\\Tests\\', $minion->getAppNamespace());
    }

    /**
     * Fixture before no error template test case.
     *
     * @param $code
     *
     * @return void
     */
    private function fixtureDeleteErrorPageFile($code) {
        $s = DIRECTORY_SEPARATOR;
        $path = __DIR__ . "${s}src${s}Resources${s}views${s}Static${s}$code.html.twig";
        if(\file_exists($path))
            if(false !== $content = \file_get_contents($path)) {
                $this->storedErrorPages[$code] = $content;
                \unlink($path);
            }
    }

    /**
     * Fixture after no error template test case.
     *
     * @param $code
     *
     * @return void
     */
    private function fixtureRestoreErrorPageFile($code) {
        $s = DIRECTORY_SEPARATOR;
        $path = __DIR__ . "${s}src${s}Resources${s}views${s}Static${s}$code.html.twig";
        if(isset($this->storedErrorPages[$code]))
            if(\file_put_contents($path, $this->storedErrorPages[$code]) !== false)
                unset($this->storedErrorPages[$code]);
    }
}