<?php

/**
 * This file is part of the Minion package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Minion;

/**
 * {@internal Tool used onl by Minion. May be but it is NOT RECOMMENDED to be used outside package }}
 *
 * Class Utils.
 *
 * @package Minion
 * @author Damian Szczerbiński <dszczer@gmail.com>
 */
abstract class Utils
{
    /**
     * @internal
     *
     * Check if template path is valid (template exists).
     *
     * @param \Twig_Environment $env      Twig environment
     * @param string            $template Template path
     *
     * @return bool
     */
    public static function templateExists(\Twig_Environment $env, $template) {
        try {
            $env->loadTemplate($template);
        } catch(\Twig_Error_Loader $err) {
            return false;
        } catch(\Twig_Error_Syntax $err) {
            return true;
        }

        return true;
    }

    /**
     * @internal
     *
     * Fix path to match runtime system requirements, for e.g. directory separator.
     *
     * @param string $path Path to fix
     *
     * @return string Fixed path
     */
    public static function fixPath($path) {
        // fix directory separators
        $fixed = \mb_ereg_replace('[\\\\\\/]+', DIRECTORY_SEPARATOR, $path);

        return \file_exists($fixed) ? \realpath($fixed) : $fixed;
    }

    /**
     * @internal
     *
     * @param string $path Absolute or relative path to template file
     * @param array  $data Array with data visible inside file scope
     *
     * @return string Compiled template content
     *
     * @throws \InvalidArgumentException Path is not valid or does not exist
     */
    public static function renderPhpTemplate($path, array $data = []) {
        \ob_start();
        \extract($data);
        $real = \realpath($path);

        if($real === false)
            throw new \InvalidArgumentException("Path '$path' is not valid or does not exist");

        include $real;

        return \ob_get_clean();
    }
}