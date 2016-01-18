<?php

/**
 * This file is part of the Minion\Tests package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Tests\Service;

use Minion\Service\ServiceConfig;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class ServiceConfigTest.
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class ServiceConfigTest extends \PHPUnit_Framework_TestCase
{
    private $id;
    private $data = [];
    private $data_backup = [];

    /**
     * Fixture setup.
     *
     * @return void
     */
    public function setUp() {
        $this->id = 'srv';
        // setup valid service config
        $this->data = $this->data_backup = [
            'class' => 'Minion\\Tests\\Service\\ServiceProvider',
            'options' => [
                'test_service.option' => 'hello world'
            ],
            'tags' => [
                'test.tag'
            ]
        ];
    }

    /**
     * Fixture tear down.
     *
     * @return void
     */
    public function tearDown() {
        $this->data = $this->data_backup;
    }

    /**
     * Test service config parser - should not throw any errors.
     *
     * @return void
     */
    public function testParser() {
        $config = new ServiceConfig($this->id, $this->data);
        $tags = $config->getTags();

        $this->assertEquals($this->id, $config->getId());
        $this->assertEquals('Minion\\Tests\\Service\\ServiceProvider', $config->getProviderClass());
        $this->assertEquals('hello world', $config->getOption('test_service.option'));
        $this->assertArrayHasKey(0, $tags);
        $this->assertEquals('test.tag', $tags[0]);
    }

    /**
     * Test parser valiator - missing class attribute.
     *
     * @return void
     */
    public function testMalformedMissingClass() {
        unset($this->data['class']);
        try{
            new ServiceConfig($this->id, $this->data);
        } catch(InvalidConfigurationException $e) {
            $this->assertEquals("Missing 'class' for '$this->id' service", $e->getMessage());
        }
    }

    /**
     * Test parser valiator - class is not a string callable.
     *
     * @return void
     */
    public function testMalformedClassNotString() {
        $this->data['class'] = [$this, 'setUp'];
        try{
            new ServiceConfig($this->id, $this->data);
        } catch(InvalidConfigurationException $e) {
            $this->assertEquals("Class argument is not a string", $e->getMessage());
        }
    }

    /**
     * Test parser valiator - class is not a string callable.
     *
     * @return void
     */
    public function testMalformedClassNotExists() {
        $this->data['class'] = 'blah';
        try{
            new ServiceConfig($this->id, $this->data);
        } catch(InvalidConfigurationException $e) {
            $this->assertEquals("Class '{$this->data['class']}' defined in '$this->id' not found", $e->getMessage());
        }
    }

    /**
     * Test parser valiator - option is not an array.
     *
     * @return void
     */
    public function testMalformedOptions() {
        $this->data['options'] = 'string';
        try{
            new ServiceConfig($this->id, $this->data);
        } catch(InvalidConfigurationException $e) {
            $this->assertEquals("service options must be an array", $e->getMessage());
        }
    }

    /**
     * Test parser valiator - tags is not an array.
     *
     * @return void
     */
    public function testMalformedTags() {
        $this->data['tags'] = 'string';
        try{
            new ServiceConfig($this->id, $this->data);
        } catch(InvalidConfigurationException $e) {
            $this->assertEquals("service tags must be an array", $e->getMessage());
        }
    }
}