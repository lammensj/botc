<?php

namespace Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation;

use GuzzleHttp\Command\Guzzle\RequestLocation\JsonLocation;
use GuzzleHttp\Command\Guzzle\RequestLocation\RequestLocationInterface;

/**
 * RequestLocation for json.
 *
 * @RequestLocation(
 *   id = "json",
 *   label = @Translation("JSON")
 * )
 */
class Json extends GuzzleRequestLocationBase {

  /**
   * {@inheritdoc}
   */
  protected function createInstance(): RequestLocationInterface {
    return new JsonLocation($this->getPluginId(), $this->configuration['content_type'] ?? 'application/json');
  }

}
