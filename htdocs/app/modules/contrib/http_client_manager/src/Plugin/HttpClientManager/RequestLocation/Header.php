<?php

namespace Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation;

use GuzzleHttp\Command\Guzzle\RequestLocation\HeaderLocation;
use GuzzleHttp\Command\Guzzle\RequestLocation\RequestLocationInterface;

/**
 * RequestLocation for header.
 *
 * @RequestLocation(
 *   id = "header",
 *   label = @Translation("Header")
 * )
 */
class Header extends GuzzleRequestLocationBase {

  /**
   * {@inheritdoc}
   */
  protected function createInstance(): RequestLocationInterface {
    return new HeaderLocation($this->getPluginId());
  }

}
