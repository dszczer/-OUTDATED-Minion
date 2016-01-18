<?php

/**
 * This file is part of the Minion\Tests package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Tests\Service;

/**
 * Class Service.
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class Service
{
    /** @var string Test option */
    private $option;

    /**
     * @param null|string $option
     *
     * @return void
     */
    public function setOption($option = null) {
        $this->option = $option;
    }

    /**
     * @return string
     */
    public function testMe() {
        return $this->option;
    }
}