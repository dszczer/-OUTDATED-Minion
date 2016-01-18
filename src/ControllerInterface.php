<?php

/**
 * This file is part of the Minion package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Interface ControllerInterface.
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
interface ControllerInterface
{
    /**
     * Dependency injection allowing access to framework instance and it's services.
     *
     * @param Application $container
     */
    public function setContainer(Application $container);

    /**
     * Helper, render template under provided $path.
     *
     * @param string $path      Path to the template file. If placed in sub-folders, separate them with "/"
     * @param array  $arguments Arguments passed to template
     *
     * @return Response
     */
    public function render($path, array $arguments = []);

    /**
     * Helper, render template under provided $path, but returns plain text instead of Response object.
     *
     * @param string $path      Path to the template file. If placed in sub-folders, separate them with "/"
     * @param array  $arguments Arguments passed to template
     *
     * @return string
     */
    public function renderText($path, array $arguments = []);

    /**
     * Helper, return response with 404 HTTP status code.
     *
     * @param string          $message       Message to display
     * @param \Exception|null $lastException Linked exception if any
     *
     * @return Response
     */
    public function createNotFoundException($message, \Exception $lastException = null);

    /**
     * Helper, return response with 403 HTTP status code.
     *
     * @param string          $message       Message to display
     * @param \Exception|null $lastException Linked exception if any
     *
     * @return Response
     */
    public function createNotAllowedException($message, \Exception $lastException = null);

    /**
     * Helper, generate absolute URL path.
     *
     * @param string     $route  Route name
     * @param array|null $params URL parameters (path and/or query)
     * @param integer    $flag   URL generating method
     *
     * @return string Generated URL
     */
    public function generateUrl($route, array $params = [], $flag = UrlGeneratorInterface::ABSOLUTE_URL);

    /**
     * Helper, get parameter by it's name.
     *
     * @param string $name    Parameter name
     * @param mixed  $default Default value, if parameter does not exist
     *
     * @return mixed Parameter or default value
     */
    public function getParameter($name, $default = null);
}