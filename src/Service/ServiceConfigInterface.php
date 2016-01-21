<?php

/**
 * This file is part of the Minion package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Service;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class ServiceConfigInterface.
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
interface ServiceConfigInterface
{
    /**
     * ServiceInterface constructor.
     *
     * @param string      $id            Service identifier
     * @param array       $parsedService Parsed service array data
     *
     * @return ServiceConfigInterface
     *
     * @throws InvalidConfigurationException Throws exception when $parsedService is invalid
     */
    public function __construct($id, array $parsedService);

    /**
     * Get all associated tags.
     *
     * @return array
     */
    public function getTags();

    /**
     * Get option by it's name.
     *
     * @param string $name Option name
     *
     * @return mixed Option value
     *
     * @throws \InvalidArgumentException Tag is not defined
     */
    public function getOption($name);

    /**
     * Get all service options.
     *
     * @return array
     */
    public function getOptions();

    /**
     * Get service identifier.
     *
     * @return string
     */
    public function getId();

    /**
     * Get fully qualified service provider class name.
     *
     * @return string
     */
    public function getProviderClass();
}