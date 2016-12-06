<?php

/**
 * This file is part of the Minion package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Console.
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class Console extends Application
{
    /** @var  string Command path */
    public $commandPath;

    /**
     * Console constructor.
     *
     * @param string $appNamespace Application namespace (autodetect, if empty)
     * @param array  $values       Default options
     * @param array  $fixPaths     Path fixes
     *
     * @return Console
     */
    public function __construct($appNamespace = '', array $values = [], array $fixPaths = []) {
        parent::__construct($appNamespace, $values, $fixPaths);

        $this->commandPath = isset($fixPaths['commandDir'])
            ? $fixPaths['commandDir']
            : Utils::fixPath($this->getRootDir() . '/src/Command/');

        $this->loadCommands($this->getCommandPath());

        // load Propel commands
        if($this['minion.usePropel']) {
            $this->loadCommands(Utils::fixPath(($this['environment'] === 'test' ? $this->getRootDir() . '/../'
                    : $this->getRootDir()) . '/vendor/propel/propel/src/Propel/Generator/Command/'), 'propel'
            );
            // replace default config-dir value command option
            /** @var Command $command */
            foreach($this['console']->all('propel') as $command) {
                $def = $command->getDefinition();
                $old = $def->getOptions();
                foreach($old as $i => $o)
                    if($o->getName() === 'config-dir') {
                        unset($old[$i]);
                        break;
                    }
                $opt = new InputOption('config-dir', null, InputOption::VALUE_REQUIRED,
                    'The directory where the configuration file is placed.', $this->getPackageDir());
                $def->setOptions(\array_merge($old, [$opt]));
            }
        }
    }

    /**
     * Get project command directory path.
     *
     * @return string
     */
    public function getCommandPath() {
        return $this->commandPath;
    }

    /**
     * Run console.
     *
     * @param Request $request Request object (not used in commands). Only to match inheritance requirements.
     *
     * @return mixed
     */
    public function run(Request $request = null) {
        $this->boot();

        return $this['console']->run();
    }

    /**
     * Load all Command files from $path directory.
     *
     * Traverse all files inside specified directory, but loads only those files with suffix 'Command.php'.
     *
     * @param string      $path   Directory with Command files
     * @param string|null $prefix Commands namespace
     *
     * @return void
     */
    public function loadCommands($path, $prefix = null) {
        // load commands
        try {
            $iterator = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);

            /** @var \RecursiveDirectoryIterator $child */
            foreach($iterator as $child)
                if($child->isFile() && \preg_match('/Command.php$/', $child->getBasename())) {
                    $namespace = $class = "";
                    $gettingNamespace = $gettingClass = false;
                    foreach(\token_get_all(\file_get_contents($child->getRealPath())) as $token) {
                        if(\is_array($token) && ($token[0] === T_ABSTRACT || $token[0] === T_INTERFACE)) {
                            $namespace = $class = '';
                            break;
                        }
                        if(\is_array($token) && $token[0] === T_NAMESPACE)
                            $gettingNamespace = true;
                        if(\is_array($token) && $token[0] === T_CLASS)
                            $gettingClass = true;
                        if($gettingNamespace === true)
                            if(\is_array($token) && \in_array($token[0], [T_STRING, T_NS_SEPARATOR]))
                                $namespace .= $token[1];
                            else if($token === ';')
                                $gettingNamespace = false;
                        if($gettingClass === true)
                            if(\is_array($token) && $token[0] === T_STRING) {
                                $class = $token[1];
                                break;
                            }
                    }

                    $className = $namespace ? $namespace . '\\' . $class : $class;
                    if(\preg_match('/Command$/', $className) > 0) {
                        // make sure file with class is loaded
                        require_once $child->getRealPath();
                        /** @var Command $command */
                        $command = new $className;
                        if($prefix !== null)
                            $command->setName($prefix . ':' . $command->getName());
                        $this['console']->add($command);
                    }
                }
        } catch(\UnexpectedValueException $ex) {
            // do nothing - no commands to load
        }
    }
}