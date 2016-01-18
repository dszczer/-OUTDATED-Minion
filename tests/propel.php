<?php

namespace Minion\Tests\Propel\Config;

use Minion\Utils;

/**
 * Test environment propel configuratin file
 *
 * @package Minion\Tests
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */

$root = __DIR__;
$arr = [
    'propel' => [
        'database' => [
            'connections' => [
                'test' => [
                    'adapter' => 'sqlite',
                    'dsn' => 'sqlite::memory:',
                    'user' => '',
                    'password' => '',
                ],
            ],
        ],
        'paths' => [
            'projectDir' => $root,
            'schemaDir' => $root . Utils::fixPath('/app'),
            'outputDir' => $root . Utils::fixPath('/src/Propel'),
            'phpDir' => $root . Utils::fixPath('/src/Propel'),
            'phpConfDir' => $root . Utils::fixPath('/app/propel/runtime'),
            'migrationDir' => $root . Utils::fixPath('/app/propel/migration'),
            'sqlDir' => $root . Utils::fixPath('/app/propel/sql'),
        ],
        'runtime' => [
            'defaultConnection' => 'test',
            'connections' => ['test'],
        ],
        'generator' => [
            'defaultConnection' => 'test',
            'connections' => ['test'],
            'objectModel' => [
                'addHooks' => false
                // slightly performance improvement
            ],
        ],
    ],
];

return $arr;
