<?php

/*
 * This file is part of the Guzzle description loader package.
 *
 * (c) Gordon Franke <info@nevalon.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guzzle\Service\Loader;

use Guzzle\Service\Loader\FileLoader;

/**
 * Class PhpLoader.
 *
 * @package Guzzle\Service\Loader
 */
class PhpLoader extends FileLoader
{
    /**
     * {@inheritdoc}
     */
    public function loadResource($resource)
    {
        return require $resource;
    }
  
    /**
     * {@inheritdoc}
     */
    public function supports(mixed $resource, string $type = null): bool
    {
        return is_string($resource) && 'php' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}
