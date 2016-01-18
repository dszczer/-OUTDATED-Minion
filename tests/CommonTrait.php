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
use Minion\Console;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Trait CommonTrait.
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
trait CommonTrait
{
    /** @var string */
    private $fixedRootDir;
    /** @var string */
    private $fixedPackageDir;

    /**
     * Set paths used inside framework, explicitly for testing case.
     *
     * @return void
     */
    private function commonSetPaths() {
        $this->fixedRootDir = __DIR__;
        $this->fixedPackageDir = __DIR__;
    }

    /**
     * Initialize application for test environment.
     *
     * @param bool $debug Debug mode
     *
     * @return Application
     */
    private function commonInitFramework($debug = true) {
        $this->commonSetPaths();
        $app = new Application('Minion\\Tests', [
            'debug' => $debug,
            'environment' => 'test',
        ], [
                'rootDir' => $this->fixedRootDir,
                'packageDir' => $this->fixedPackageDir,
                'propelConfigPath' => __DIR__ . DIRECTORY_SEPARATOR . 'propel.php',
            ]
        );

        return $app;
    }

    /**
     * Initialize console for test environment.
     *
     * @param bool $debug Debug mode
     *
     * @return ApplicationTester
     */
    private function commonInitConsole($debug = true) {
        $this->commonSetPaths();
        $app = new Console('Minion\\Tests', [
            'debug' => $debug,
            'environment' => 'test',
        ], [
                'rootDir' => $this->fixedRootDir,
                'packageDir' => $this->fixedPackageDir,
                'propelConfigPath' => __DIR__ . DIRECTORY_SEPARATOR . 'propel.php',
                'commandDir' => $this->fixedRootDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Command',
            ]
        );
        $console = $app['console'];
        $console->setAutoExit(false);
        $console->setCatchExceptions(false);

        $tester = new ApplicationTester($app['console']);

        return $tester;
    }

    /**
     * Run application without error handlers to simplify error output (instead of HTML error page).
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response Request response
     */
    private function commonRunRaw(Application $app, Request $request) {
        $response = $app->handle($request, HttpKernelInterface::MASTER_REQUEST, false);
        $app->terminate($request, $response);

        return $response;
    }
}