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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class Controller.
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
abstract class Controller implements ControllerInterface
{
    /** @var Application Framework */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(Application $container) {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function render($path, array $arguments = []) {
        return new Response($this->renderText($path, $arguments));
    }

    /**
     * {@inheritdoc}
     */
    public function renderText($path, array $arguments = []) {
        if($this->container['minion.useTwig'])
            return $this->container['twig']->render($path, $arguments);
        else
            return function($this, $path, $arguments) {
                \ob_start();
                \extract($arguments);
                include \Minion\Utils::fixPath($this->container->getRootDir() . '/src/' . $path);
                $content = \ob_get_clean();

                return $content;
            };
    }

    /**
     * {@inheritdoc}
     */
    public function createNotFoundException($message, \Exception $lastException = null) {
        $exception = new NotFoundHttpException($message, $lastException);

        return $this->container->minionError($exception, 404);
    }

    /**
     * {@inheritdoc}
     */
    public function createNotAllowedException($message, \Exception $lastException = null) {
        $exception = new AccessDeniedHttpException($message, $lastException);

        return $this->container->minionError($exception, 403);
    }

    /**
     * {@inheritdoc}
     */
    public function generateUrl($route, array $params = [], $flag = UrlGeneratorInterface::ABSOLUTE_URL) {
        return $this->container['url_generator']->generate($route, $params, $flag);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name, $default = null) {
        return $this->container['parameters']->get($name, $default);
    }
}