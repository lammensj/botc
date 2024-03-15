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
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class YamlLoader.
 *
 * @package Guzzle\Service\Loader
 */
class YamlLoader extends FileLoader
{
  
    /**
     * The Yaml Parser.
     */
    private $yamlParser;

    /**
     * {@inheritdoc}
     */
    public function loadResource($resource)
    {
        if (null === $this->yamlParser) {
            if (!class_exists('Symfony\Component\Yaml\Parser')) {
                throw new \LogicException('Loading translations from the YAML format requires the Symfony Yaml component.');
            }
            $this->yamlParser = new YamlParser();
        }

        $configValues = $this->yamlParser->parse(file_get_contents($resource));

        return $configValues;
    }
  
    /**
     * {@inheritdoc}
     */
    public function supports(mixed $resource, string $type = null): bool
    {
        return is_string($resource) && 'yml' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}
