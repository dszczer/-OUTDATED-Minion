<?php

/**
 * This file is part of the Minion package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Twig;

use Minion\Service\ServiceProvider;
use Silex\Application as SilexApp;

/**
 * {@internal it is NOT RECOMMENDED to use it outside Minion package}}
 *
 * Class TwigExtensionTagServiceProvider.
 *
 * Allow to extend twig environment by registering extension as a service.
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class TwigExtensionTagServiceProvider extends ServiceProvider
{
    /**
     * Extend Twig Environment with provided Extension.
     *
     * @param SilexApp $app
     *
     * @return void
     */
    public function register(SilexApp $app) {
        $app['twig'] = $app->share($app->extend('twig', function (\Twig_Environment $twig, SilexApp $app) {
            $class = $this->getServiceConfig()->getProviderClass();
            $twig->addExtension(new $class);

            return $twig;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function boot(SilexApp $app) {}
}