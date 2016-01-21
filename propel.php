<?php

/**
 * This file is part of the Minion\Tests package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Propel\Config;

use Minion\Utils;

/**
 * Propel default configuration file.
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */

$root = \realpath(__DIR__ . '/../../../');
$arr = \array_replace_recursive([
    'propel' => [
        'paths' => [
            'projectDir' => $root,
            'schemaDir' => $root . Utils::fixPath('/app'),
            'outputDir' => $root . Utils::fixPath('/src/Propel'),
            'phpDir' => $root . Utils::fixPath('/src/Propel'),
            'phpConfDir' => $root . Utils::fixPath('/app/propel/runtime'),
            'migrationDir' => $root . Utils::fixPath('/app/propel/migration'),
            'sqlDir' => $root . Utils::fixPath('/app/propel/sql')
        ],
        'runtime' => [
            'defaultConnection' => 'default',
            'connections' => ['default'],
            'log' => [
                'defaultLogger' => [
                    'type' => 'stream',
                    'level' => 300,
                    'path' => $root . Utils::fixPath('/var/log/propel.log')
                ]
            ]
        ],
        'generator' => [
            'defaultConnection' => 'default',
            'connections' => ['default'],
            'objectModel' => [
                'addHooks' => false // slightly performance improvement
            ]
        ]
    ]
], include($root . Utils::fixPath('/propel.php')));

return $arr;
