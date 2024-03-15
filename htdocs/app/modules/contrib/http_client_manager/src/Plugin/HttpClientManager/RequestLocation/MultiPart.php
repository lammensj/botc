<?php

namespace Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation;

use GuzzleHttp\Command\Guzzle\RequestLocation\MultiPartLocation;
use GuzzleHttp\Command\Guzzle\RequestLocation\RequestLocationInterface;

/**
 * RequestLocation ofr multiPart.
 *
 * @RequestLocation(
 *   id = "multiPart",
 *   label = @Translation("Multi-part")
 * )
 */
class MultiPart extends GuzzleRequestLocationBase {

  /**
   * {@inheritdoc}
   */
  protected function createInstance(): RequestLocationInterface {
    return new MultiPartLocation($this->getPluginId());
  }

}
