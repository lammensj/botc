<?php

namespace Drupal\eca_tamper\Plugin\ECA\Condition;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\tamper\TamperManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derive any Tamper plugin into an ECA condition.
 */
class TamperDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * Supported categories of tamper plugins.
   *
   * @var string[]
   */
  protected static array $supportedCategories = [
    'Text',
    'Date/time',
    'Number',
    'Other',
  ];

  /**
   * The tamper plugin manager.
   *
   * @var \Drupal\tamper\TamperManagerInterface
   */
  protected TamperManagerInterface $tamperManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    $plugin = new static();
    $plugin->tamperManager = $container->get('plugin.manager.tamper');
    return $plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    $this->derivatives = [];
    foreach ($this->tamperManager->getDefinitions() as $definition) {
      if (!in_array($definition['category'], self::$supportedCategories, TRUE)) {
        continue;
      }
      $this->derivatives[$definition['id']] = [
        'id' => 'eca_tamper:' . $definition['id'],
        'label' => $this->t('Tamper: @label', ['@label' => $definition['label']->render()]),
        'description' => $definition['description'],
        'category' => $definition['category'],
        'tamper_plugin' => $definition['id'],
      ] + $base_plugin_definition;
    }
    return $this->derivatives;
  }

}
