<?php

/**
 * This file is part of the Minion\Tests package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Tests\Twig;

/**
 * Class TagExtensionTest
 *
 * @package Minion\Tests
 * @author  Damian Szczerbiński <dszczer@gmail.com>
 */
class TagExtensionTest extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'twig_tag_test_extension';
    }
}