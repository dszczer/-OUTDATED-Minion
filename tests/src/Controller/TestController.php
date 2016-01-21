<?php

/**
 * This file is part of the Minion\Tests package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Tests\Controller;

use Minion\Application;
use Minion\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TestController.
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class TestController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function testAction(Request $request, Application $app) {
        return $this->render('test.html.twig', ['arg' => 'sample argument']);
    }

    /**
     * @param Request $request
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testError500Action(Request $request) {
        throw new \Exception('error 500');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function testError403Action(Request $request) {
        return $this->createNotAllowedException('error 403');
    }
}