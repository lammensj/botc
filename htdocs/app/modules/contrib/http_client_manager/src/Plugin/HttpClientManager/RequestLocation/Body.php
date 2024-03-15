<?php

namespace Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation;

use GuzzleHttp\Command\Guzzle\RequestLocation\BodyLocation;
use GuzzleHttp\Command\Guzzle\RequestLocation\RequestLocationInterface;

/**
 * RequestLocation for body.
 *
 * @RequestLocation(
 *   id = "body",
 *   label = @Translation("Body")
 * )
 */
class Body extends GuzzleRequestLocationBase {

  /**
   * {@inheritdoc}
   */
  protected function createInstance(): RequestLocationInterface {
    return new BodyLocation($this->getPluginId());
  }

}
