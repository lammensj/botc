<?php

namespace Drupal\http_client_manager\RequestLocation;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;

/**
 * Base class for Guzzle RequestLocations.
 */
abstract class RequestLocationBase extends PluginBase implements RequestLocationInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configuration = NestedArray::mergeDeep($this->defaultConfiguration(), $this->configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration(): array {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration): void {
    $this->configuration = $configuration;
  }

}
