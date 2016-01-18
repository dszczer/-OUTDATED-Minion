<?php

/**
 * This file is part of the Minion package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion;

use Knp\Provider\ConsoleServiceProvider;
use Minion\Service\Service;
use Minion\Service\ServiceConfig;
use Minion\Service\ServiceProviderInterface;
use Minion\Twig\AssetExtension;
use Minion\Twig\MiscExtension;
use Minion\Twig\TwigExtensionTagServiceProvider;
use Minion\Twig\UrlExtension;
use Propel\Runtime\Exception\InvalidArgumentException;
use Propel\Silex\PropelServiceProvider;
use Silex\Application as SilexApp;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Application.
 *
 * Wrapper for Silex framework to improve it's flexibility and usability.
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class Application extends SilexApp
{
    const VERSION = '1.0.0';
    /** @var string Project root directory */
    protected $rootDir;
    /** @var string Package directory */
    protected $packageDir;
    /** @var string Project configuration path */
    protected $configPath;
    /** @var string Propel configuration file */
    protected $propelConfigPath;
    /** @var string Application namespace */
    protected $appNamespace;

    /**
     * Get application namespace.
     *
     * @return string
     */
    public function getAppNamespace() {
        return $this->appNamespace;
    }

    /**
     * Get project root directory.
     *
     * @return string
     */
    public function getRootDir() {
        return $this->rootDir;
    }

    /**
     * get Minion package directory.
     *
     * @return string
     */
    public function getPackageDir() {
        return $this->packageDir;
    }

    /**
     * Get default Propel configuration path.
     *
     * @return string
     */
    public function getPropelConfigPath() {
        return $this->propelConfigPath;
    }

    /**
     * Get configuration path relative to the root directory.
     *
     * @return string
     */
    public function getConfigPath() {
        return $this->configPath;
    }

    /**
     * Application constructor.
     *
     * @param string $appNamespace Application and/or vendor namespace, e.g. 'namespace', 'vendor\\namespace'
     * @param array  $values       Custom configuration
     * @param array  $fixPaths     Fix paths used to bootstrap files and directories
     *
     * @return Application
     *
     * @throws \Exception
     */
    public function __construct($appNamespace = '', array $values = [], array $fixPaths = []) {
        // big error catch for nice debug
        try {
            $defaultValues = [
                'environment' => 'prod',
                'minion.usePropel' => true,
                'minion.useTwig' => true,
            ];

            // default configuration
            parent::__construct(\array_replace_recursive($defaultValues, $values));

            // error handler
            $this->error(function(\Exception $ex, $code) {
                return $this->minionError($ex, $code);
            });

            // calculate directories and paths
            $this->resolvePaths($fixPaths);

            // determine project namespace, if not defined
            $realNamespace = $appNamespace;
            $jsonPath = Utils::fixPath($this->getRootDir() . '/composer.json');
            if(!$appNamespace && \file_exists($jsonPath)) {
                $json = \json_decode(\file_get_contents($jsonPath), true);
                if(isset($json['autoload']) && \is_array($json['autoload']))
                    foreach($json['autoload'] as $psr)
                        if(\is_array($psr))
                            foreach($psr as $namespace => $path)
                                if(\preg_match('/^[\\/\\\\]?src[\\/\\\\]?/', $path))
                                    $realNamespace = $namespace;
            }
            if(!$realNamespace)
                throw new \InvalidArgumentException('Cannot resolve project namespace.');
            $this->appNamespace = $realNamespace;

            // register services
            $this->register(new MonologServiceProvider(), [
                'monolog.logfile' => $this->getRootDir() . Utils::fixPath('/var/log/') .
                    ($this['debug'] ? 'dev.log' : 'prod.log'),
                'monolog.name' => $this->getAppNamespace(),
            ]);

            if($this['minion.useTwig']) {
                $this->register(new TwigServiceProvider(), [
                    'twig.path' => $this->getRootDir() . Utils::fixPath('/src/Resources/views'),
                    'twig.options' => [
                        'cache' => $this->getRootDir() . Utils::fixPath('/var/cache/twig'),
                    ],
                ]);
                // load Twig Extensions
                $this['twig']->addExtension(new AssetExtension($this));
                $this['twig']->addExtension(new UrlExtension($this));
                $this['twig']->addExtension(new MiscExtension($this));
            }

            $this->register(new UrlGeneratorServiceProvider());

            if($this['minion.usePropel']) {
                $propelConfig = include Utils::fixPath($this->getPropelConfigPath());
                if(isset($propelConfig['propel']['paths']['phpConfDir'])
                    && \file_exists($propelCompiledCfg = Utils::fixPath($propelConfig['propel']['paths']['phpConfDir']
                        . '/config.php'))
                )
                    $this->register(new PropelServiceProvider(), [
                        'propel.config_file' => $propelCompiledCfg,
                    ]);
                else throw new InvalidArgumentException('Missing Propel compiled configuration file. Use "console propel:config:convert" in console');
            }

            $this->register(new ConsoleServiceProvider(), [
                    'console.name' => $this->getAppNamespace(),
                    'console.version' => self::VERSION,
                    'console.project_directory' => $this->getRootDir(),
                ]
            );

            // load configuration
            $loader = new YamlFileLoader(new FileLocator($this->getRootDir() . $this->getConfigPath()));
            /** @var RouteCollection $routes */
            $routes = $loader->load('routing.yml');
            // fix controller namespaces
            if(\count($routes) > 0)
                foreach($routes as $route)
                    /** @var Route $route */
                    if($route->hasDefault('_controller'))
                        $route->setDefault('_controller', $this->getAppNamespace() . '\\Controller\\'
                            . $route->getDefault('_controller')
                        );
            $this['routes']->addCollection($routes);

            $parametersFile = $this->getRootDir() . $this->getConfigPath() . 'parameters.yml';
            if(!\file_exists($parametersFile))
                throw new \RuntimeException('File parameters.yml does not exist or is not accessible');
            /** @var array $parameters */
            $parameters = Yaml::parse(\file_get_contents($parametersFile)) ?: [];
            $this['parameters'] = new ParameterBag($parameters);

            // load user-defined configuration and services
            $userConfigFile = $this->getRootDir() . $this->getConfigPath() . 'config.yml';
            if(\file_exists($userConfigFile)) {
                /** @var array $userConfig */
                $userConfig = Yaml::parse(\file_get_contents($userConfigFile) ?: '') ?: [];
                if(isset($userConfig['config']))
                    $this['config'] = $userConfig['config'];
                if(isset($userConfig['services']))
                    /**
                     * @var string $id
                     * @var array  $def
                     */
                    foreach($userConfig['services'] as $id => $def) {
                        $serviceConfig = new ServiceConfig($id, $def);
                        $class = $serviceConfig->getProviderClass();

                        if(\count($serviceConfig->getTags()) === 0) {
                            /** @var ServiceProviderInterface $provider */
                            $provider = new $class;
                            if(!$provider instanceof ServiceProviderInterface)
                                throw new InvalidConfigurationException(
                                    "Service '$id' provider must be instance of \\Minion\\Service\\ServiceProviderInterface, "
                                    . \get_class($provider) . " given");
                            $provider->setServiceConfig($serviceConfig);
                            $this->register($provider, $serviceConfig->getOptions());
                        } else {
                            $calledTags = [];
                            foreach($serviceConfig->getTags() as $tag) {
                                if(\in_array($tag, $calledTags)) continue;
                                switch($tag) {
                                    case 'twig.extension':
                                        $calledTags[] = $tag;
                                        if(!$this['minion.useTwig'])
                                            throw new InvalidConfigurationException(
                                                "Service '$id' uses 'twig.extension' tag when Twig Environment is not enabled");
                                        $provider = new TwigExtensionTagServiceProvider();
                                        $provider->setServiceConfig($serviceConfig);
                                        $this->register($provider, $serviceConfig->getOptions());
                                        break;
                                }
                            }
                        }
                    }
            }

            // register events
            // inject self to the controller automatically
            $this['dispatcher']->addListener(KernelEvents::CONTROLLER, function(FilterControllerEvent $event) {
                /** @var Controller $controller */
                $controller = $event->getController()[0];
                $controller->setContainer($this);
            });
        } catch(\Exception $ex) {
            if($this['environment'] == 'test')
                throw $ex;
            else
                $this->fastAbort($ex);
        }
    }

    /**
     * Handle errors and exceptions thrown by entire application.
     *
     * @param \Exception $ex   Any exception
     * @param integer    $code HTTP status code
     *
     * @return Response Response to the client with nice error page
     */
    public function minionError(\Exception $ex, $code) {
        $handler = new ExceptionHandler($this['debug']);
        $exception = FlattenException::create($ex);
        $response = Response::create($handler->getHtml($exception), $code, $exception->getHeaders())
            ->setCharset(\ini_get('default_charset'))
        ;

        if($this['debug'])
            return $response;
        else {
            $twig = $this['twig'];
            $blank = new Response();
            $blankTemplate = <<<'HTML'
<!DOCTYPE html>
<html>
    <head><title>Error %d</title></head>
    <body><h1>Error %d occured</h1></body>
</html>
HTML;

            switch($code) {
                case 403:
                    $tpl = 'Static/403.html.twig';
                    if(!Utils::templateExists($twig, $tpl)) {
                        $blank->setStatusCode(403);
                        $blank->setContent(\str_replace('%d', 403, $blankTemplate));
                        return $blank;
                    }
                    $response->setContent($twig->render($tpl, ['exception' => $ex]));
                    break;
                case 404:
                    $tpl = 'Static/404.html.twig';
                    if(!Utils::templateExists($twig, $tpl)) {
                        $blank->setStatusCode(404);
                        $blank->setContent(\str_replace('%d', 404, $blankTemplate));
                        return $blank;
                    }
                    $response->setContent($twig->render($tpl, ['exception' => $ex]));
                    break;
                default:
                case 500:
                    $tpl = 'Static/500.html.twig';
                    if(!Utils::templateExists($twig, $tpl)) {
                        $blank->setStatusCode(500);
                        $blank->setContent(\str_replace('%d', 500, $blankTemplate));
                        return $blank;
                    }
                    $response->setContent($twig->render($tpl, ['exception' => $ex]));
                    break;
            }

            return $response;
        }
    }

    /**
     * Terminate application immediately and show exception to the client. Does not trigger any events.
     *
     * @param \Exception $exception Exception instance
     * @param integer    $code      HTTP status code
     *
     * @return void
     */
    public function fastAbort(\Exception $exception, $code = 500) {
        $response = $this->minionError($exception, $code);
        $response->send();
        die;
    }

    /**
     * Resolve paths to navigate through project.
     *
     * @param array $fixPaths User-defined path fixes
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function resolvePaths(array $fixPaths) {
        $this->rootDir = \realpath(isset($fixPaths['rootDir']) ? $fixPaths['rootDir'] : __DIR__ . '/../../../../');
        $this->packageDir = \realpath(isset($fixPaths['packageDir']) ? $fixPaths['packageDir'] : __DIR__ . '/../');
        $this->configPath = Utils::fixPath(isset($fixPaths['configPath']) ? $fixPaths['configPath'] : '/app/');

        if($this->rootDir === false || $this->packageDir === false)
            throw new \InvalidArgumentException('Bootstrap directories do not exists or are not accessible');

        if($this['minion.usePropel']) {
            $this->propelConfigPath = \realpath(isset($fixPaths['propelConfigPath']) ? $fixPaths['propelConfigPath']
                : Utils::fixPath($this->packageDir . '/propel.php')
            );
            if($this->propelConfigPath === false)
                throw new \InvalidArgumentException('Propel configuration file in vendor Minion not found');
        }
    }
}