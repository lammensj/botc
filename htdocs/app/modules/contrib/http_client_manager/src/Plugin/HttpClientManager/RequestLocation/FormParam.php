<?php

namespace Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation;

use GuzzleHttp\Command\Guzzle\RequestLocation\FormParamLocation;
use GuzzleHttp\Command\Guzzle\RequestLocation\RequestLocationInterface;

/**
 * RequestLocation for form param.
 *
 * @RequestLocation(
 *   id = "formParam",
 *   label = @Translation("Form param")
 * )
 */
class FormParam extends GuzzleRequestLocationBase {

  /**
   * {@inheritdoc}
   */
  protected function createInstance(): RequestLocationInterface {
    return new FormParamLocation($this->getPluginId());
  }

}
