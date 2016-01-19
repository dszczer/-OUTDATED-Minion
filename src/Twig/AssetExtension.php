<?php

/**
 * This file is part of the Minion package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion\Twig;

use Minion\Application;
use Minion\Utils;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AssetExtension.
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
class AssetExtension extends \Twig_Extension
{
    /** @var Application Application container */
    private $container;

    /**
     * AssetExtension constructor.
     * Inject dependencies
     *
     * @param Application $app Framework
     *
     * @return AssetExtension
     */
    public function __construct(Application $app) {
        $this->container = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'minion_twig_asset';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return [
            new \Twig_Function('asset', [
                    $this,
                    'assetFunction',
                ]
            ),
        ];
    }

    /**
     * Return fixed asset's path.
     *
     * @param string $asset      Path to the asset
     * @param bool   $serverPath server-related path or web-related
     *
     * @throws FileException
     *
     * @return string
     */
    public function assetFunction($asset, $serverPath = false) {
        /** @var Request|null $request */
        $request = isset($this->container['request']) ? $this->container['request'] : null;
        $path = \ltrim($asset, '/\\');
        $assetPath = Utils::fixPath($this->container->getRootDir() . '/web/' . $path);

        if(!\file_exists($assetPath))
            throw new FileException("Asset '$asset' with path '$assetPath' not found");

        if(!$serverPath)
            if($request instanceof Request)
                $assetPath = $request->getSchemeAndHttpHost() . '/' . $path;
            else
                $assetPath = '/' . $path;

        return $assetPath;
    }
}