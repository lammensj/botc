<?php

namespace Drupal\http_client_manager\RequestLocation;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use GuzzleHttp\Command\Guzzle\RequestLocation\RequestLocationInterface as GuzzleRequestLocationInterface;

/**
 * Interface for Guzzle RequestLocations.
 */
interface RequestLocationInterface extends ConfigurableInterface, PluginInspectionInterface, GuzzleRequestLocationInterface {

}
