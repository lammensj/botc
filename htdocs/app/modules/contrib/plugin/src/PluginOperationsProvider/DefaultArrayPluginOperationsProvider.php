<?php

namespace Drupal\plugin\PluginOperationsProvider;

use Drupal\Component\Serialization\Json;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\plugin\PluginOperationsProviderInterface;
use Drupal\plugin\PluginType\PluginTypeInterface;

/**
 * Default operation provider for array definition plugin types.
 */
class DefaultArrayPluginOperationsProvider implements PluginOperationsProviderInterface, PluginTypeAwarePluginOperationsProviderInterface {

  use StringTranslationTrait;

  /**
   * The plugin type.
   *
   * @var \Drupal\plugin\PluginType\PluginTypeInterface
   */
  protected $pluginType;

  /**
   * {@inheritdoc}
   */
  public function getOperations($plugin_id) {
    return [
      'view' => [
        'title' => $this->t('View'),
        'url' => Url::fromRoute('plugin.plugin.detail', [
          'plugin_type' => $this->pluginType->getId(),
          'plugin_id' => $plugin_id,
        ]),
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 700,
            'minHeight' => 500,
          ]),
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginType(PluginTypeInterface $plugin_type) {
    $this->pluginType = $plugin_type;
  }

}
