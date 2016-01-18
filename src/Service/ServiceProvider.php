<?php

/**
 * This file is part of the Minion package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Service;

use Silex\Application as SilexApp;

/**
 * Class ServiceProvider
 * Basic Service provider class
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
abstract class ServiceProvider implements ServiceProviderInterface
{
    /** @var ServiceConfig ServiceConfig object */
    protected $serviceConfig;

    /**
     * {@inheritdoc}
     */
    public function setServiceConfig(ServiceConfig $service) {
        $this->serviceConfig = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceConfig() {
        return $this->serviceConfig;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function register(SilexApp $app);

    /**
     * {@inheritdoc}
     */
    abstract public function boot(SilexApp $app);
}