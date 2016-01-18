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
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class ServiceConfig.
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class ServiceConfig implements ServiceConfigInterface
{
    /** @var string Service identifier */
    protected $id;
    /** @var string Service provider fully qualified class name */
    protected $providerClass;
    /** @var array Service related tags */
    protected $tags;
    /** @var ParameterBag Service options */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function __construct($id, array $parsed) {
        $class = null;
        $options = $tags = [];
        if(isset($parsed['class']))
            if(\is_string($parsed['class']))
                if(\class_exists($parsed['class']))
                    $class = $parsed['class'];
                else throw new InvalidConfigurationException("Class '{$parsed['class']}' defined in '$id' not found");
            else throw new InvalidConfigurationException("Class argument is not a string");
        else throw new InvalidConfigurationException("Missing 'class' for '$id' service");
        if(isset($parsed['options'])) {
            if(\is_array($parsed['options']))
                $options = $parsed['options'];
            else throw new InvalidConfigurationException("service options must be an array");
        }
        if(isset($parsed['tags'])) {
            if(\is_array($parsed['tags']))
                $tags = $parsed['tags'];
            else throw new InvalidConfigurationException("service tags must be an array");
        }

        $this->id = $id;
        $this->providerClass = $class;
        $this->options = new ParameterBag($options);
        $this->tags = $tags;
    }

    /**
     * {@inheritdoc}
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function getId() {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name) {
        if(!$this->options->has($name))
            throw new \InvalidArgumentException("Option '$name' is not defined");

        return $this->options->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions() {
        return $this->options->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderClass() {
        return $this->providerClass;
    }
}