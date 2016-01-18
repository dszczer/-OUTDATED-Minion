<?php

/**
 * This file is part of the Minion package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Service;

/**
 * Interface ServiceProviderInterface
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
interface ServiceProviderInterface extends \Silex\ServiceProviderInterface
{
    /**
     * Set service configuration object into provider.
     *
     * @param ServiceConfig $service
     *
     * @return void
     */
    public function setServiceConfig(ServiceConfig $service);

    /**
     * Get service configuration.
     *
     * @return ServiceConfig
     */
    public function getServiceConfig();
}