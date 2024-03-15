<?php

namespace Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation;

use GuzzleHttp\Command\Guzzle\RequestLocation\QueryLocation;
use GuzzleHttp\Command\Guzzle\RequestLocation\RequestLocationInterface;

/**
 * RequestLocation for query.
 *
 * @RequestLocation(
 *   id = "query",
 *   label = @Translation("Query")
 * )
 */
class Query extends GuzzleRequestLocationBase {

  /**
   * {@inheritdoc}
   */
  protected function createInstance(): RequestLocationInterface {
    return new QueryLocation($this->pluginId, $this->configuration['query_serializer'] ?? NULL);
  }

}
