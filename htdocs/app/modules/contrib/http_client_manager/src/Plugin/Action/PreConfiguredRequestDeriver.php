<?php

namespace Drupal\http_client_manager\Plugin\Action;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deriver for preconfigured htrtp requests.
 */
class PreConfiguredRequestDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The action entity storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected ConfigEntityStorageInterface $entityStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    $instance = new static();
    $instance->setEntityStorage($container->get('entity_type.manager')->getStorage('http_config_request'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    $this->derivatives = [];
    /** @var \Drupal\http_client_manager\Entity\HttpConfigRequestInterface $request */
    foreach ($this->entityStorage->loadMultiple() as $request) {
      $id = $request->id();
      $this->derivatives[$id] = [
        'label' => 'Pre-configured: ' . $request->label(),
        'action_entity_id' => $id,
      ] + $base_plugin_definition;
    }
    return $this->derivatives;
  }

  /**
   * Set the entity storage.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $storage
   *   The entity storage.
   */
  public function setEntityStorage(ConfigEntityStorageInterface $storage): void {
    $this->entityStorage = $storage;
  }

}
