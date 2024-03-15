<?php

namespace Drupal\http_client_manager\RequestLocation;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\http_client_manager\Annotation\RequestLocation;

/**
 * Manages RequestLocation-plugins.
 */
class RequestLocationPluginManager extends DefaultPluginManager {

  /**
   * Constructs a RequestLocationPluginManager-instance.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/HttpClientManager/RequestLocation', $namespaces, $module_handler, RequestLocationInterface::class, RequestLocation::class);

    $this->setCacheBackend($cache_backend, 'http_client_manager_request_location');
    $this->alterInfo('http_client_manager_request_location_info');
  }

}
