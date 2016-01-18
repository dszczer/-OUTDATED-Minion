<?php

/**
 * This file is part of the Minion\Tests package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Tests\Service;

use Minion\Service\ServiceProvider as BaseServiceProvider;
use Silex\Application;

/**
 * Class ServiceProvider.
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app) {
        $app[$this->getServiceConfig()->getId()] = $app->share(function() {
            return new Service();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app) {}
}