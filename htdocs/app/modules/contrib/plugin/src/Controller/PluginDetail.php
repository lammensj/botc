<?php

namespace Drupal\plugin\Controller;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\plugin\PluginDiscovery\TypedDefinitionEnsuringPluginDiscoveryDecorator;
use Drupal\plugin\PluginType\PluginTypeInterface;
use Drupal\plugin\PluginType\PluginTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles the "plugin detail" route.
 */
class PluginDetail extends ListBase {

  /**
   * The plugin type manager.
   *
   * @var \Drupal\plugin\PluginType\PluginTypeManagerInterface
   */
  protected $pluginTypeManager;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\plugin\PluginType\PluginTypeManagerInterface
   *   The plugin type manager.
   */
  public function __construct(TranslationInterface $string_translation, ModuleHandlerInterface $module_handler, PluginTypeManagerInterface $plugin_type_manager) {
    parent::__construct($string_translation, $module_handler);
    $this->pluginTypeManager = $plugin_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('string_translation'),
      $container->get('module_handler'),
      $container->get('plugin.plugin_type_manager'),
    );
  }

  /**
   * Returns the route's title.
   *
   * @param \Drupal\plugin\PluginType\PluginTypeInterface $plugin_type
   *   The plugin type.
   * @param $plugin_id
   *   The plugin ID.
   *
   * @return string
   */
  public function title($plugin_type, $plugin_id) {
    $plugin_discovery = new TypedDefinitionEnsuringPluginDiscoveryDecorator($plugin_type);
    /** @var \Drupal\plugin\PluginDefinition\PluginDefinitionInterface $plugin_definition */
    $plugin_definition = $plugin_discovery->getDefinition($plugin_id);

    if ($plugin_definition instanceof PluginLabelDefinitionInterface) {
      return $plugin_definition->getLabel() ?: $plugin_id;
    }
    else {
      return $plugin_id;
    }
  }

  /**
   * Handles the route.
   *
   * @return mixed[]
   *   A render array.
   */
  public function execute(PluginTypeInterface $plugin_type, $plugin_id) {
    $plugin_discovery = new TypedDefinitionEnsuringPluginDiscoveryDecorator($plugin_type);
    /** @var \Drupal\plugin\PluginDefinition\PluginDefinitionInterface $plugin_definition */
    $plugin_definition = $plugin_discovery->getDefinition($plugin_id);

    // Use Devel module's dumper if it's availabel.
    if (\Drupal::getContainer()->has('devel.dumper')) {
      return \Drupal::service('devel.dumper')->exportAsRenderable($plugin_definition, $plugin_id);
    }

    // @todo Better fallback?
    $build = [
      '#markup' => 'Plugin detail requires Devel module.'
    ];

    return $build;
  }

}
