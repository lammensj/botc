<?php

namespace Drupal\plugin\PluginOperationsProvider;

use Drupal\plugin\PluginType\PluginTypeInterface;

/**
 * Interface for plugin operations providers which receive the plugin type.
 */
interface PluginTypeAwarePluginOperationsProviderInterface {

  /**
   * Sets the plugin type.
   *
   * @param \Drupal\plugin\PluginType\PluginTypeInterface $plugin_type
   *   The plugin type.
   */
  public function setPluginType(PluginTypeInterface $plugin_type);

}
