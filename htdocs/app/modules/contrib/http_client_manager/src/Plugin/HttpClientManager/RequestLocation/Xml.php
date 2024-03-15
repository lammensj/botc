<?php

namespace Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation;

use GuzzleHttp\Command\Guzzle\RequestLocation\RequestLocationInterface;
use GuzzleHttp\Command\Guzzle\RequestLocation\XmlLocation;

/**
 * RequestLocation for xml.
 *
 * @RequestLocation(
 *   id = "xml",
 *   label = @Translation("XML")
 * )
 */
class Xml extends GuzzleRequestLocationBase {

  /**
   * {@inheritdoc}
   */
  protected function createInstance(): RequestLocationInterface {
    return new XmlLocation($this->getPluginId(), $this->configuration['content_type'] ?? 'application/xml');
  }

}
