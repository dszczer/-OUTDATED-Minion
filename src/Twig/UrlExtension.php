<?php

/**
 * This file is part of the Minion package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Twig;

use Minion\Application;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class UrlExtension.
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class UrlExtension extends \Twig_Extension
{
    /** @var Application Application container */
    private $container;

    /**
     * UrlExtension constructor.
     *
     * Inject dependencies.
     *
     * @param Application $app Framework
     */
    public function __construct(Application $app) {
        $this->container = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'minion_twig_url';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return [
            new \Twig_Function('url', [$this, 'urlFunction']),
            new \Twig_Function('path', [$this, 'pathFunction']),
        ];
    }

    /**
     * Generate absolute URL path.
     *
     * @param string $route  Route name
     * @param array  $params Parameters
     *
     * @return string
     */
    public function urlFunction($route, array $params = []) {
        return $this->container['url_generator']->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Generate relative URL path.
     *
     * @param string $route  Route name
     * @param array  $params Parameters
     *
     * @return string
     */
    public function pathFunction($route, array $params = []) {
        return $this->container['url_generator']->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}