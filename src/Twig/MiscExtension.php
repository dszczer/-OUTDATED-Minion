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

/**
 * Class MiscExtension.
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class MiscExtension extends \Twig_Extension
{
    /** @var Application Application container */
    private $container;

    /**
     * MiscExtension constructor.
     * Inject dependencies
     *
     * @param Application $app Framework
     *
     * @return MiscExtension
     */
    public function __construct(Application $app) {
        $this->container = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'minion_twig_misc';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return [
            new \Twig_Function('static', [$this, 'staticFunction']),
        ];
    }

    /**
     * Call static by fully qualified name.
     *
     * @param callable $callable  Fully qualified static method or function name
     * @param array    $arguments Optional arguments
     *
     * @throws \InvalidArgumentException Callable must be string type
     *
     * @return mixed
     */
    public function staticFunction(callable $callable, array $arguments = []) {
        if(!\is_string($callable))
            throw new \InvalidArgumentException("\$callable argument must be a string callable");

        return \call_user_func_array($callable, $arguments);
    }
}