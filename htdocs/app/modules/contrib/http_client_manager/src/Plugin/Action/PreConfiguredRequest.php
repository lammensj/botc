<?php

namespace Drupal\http_client_manager\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Wrapper action for all pre-configured http requests.
 *
 * @Action(
 *   id = "http_client_manager_preconfigured_request",
 *   deriver = "\Drupal\http_client_manager\Plugin\Action\PreConfiguredRequestDeriver",
 *   nodocs = true
 * )
 */
class PreConfiguredRequest extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage interface for http config request config entities.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected ConfigEntityStorageInterface $entityStorage;

  /**
   * The private temporary key value store.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected PrivateTempStore $privateTempStore;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigEntityStorageInterface $entityStorage, PrivateTempStore $privateTempStore) {
    $this->entityStorage = $entityStorage;
    $this->privateTempStore = $privateTempStore;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): PreConfiguredRequest {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('http_config_request'),
      $container->get('tempstore.private')->get('http_client_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $access = AccessResult::allowed();
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL): void {
    [, $entityId] = explode(':', $this->getPluginId());
    /** @var \Drupal\http_client_manager\Entity\HttpConfigRequestInterface $request */
    $request = $this->entityStorage->load($entityId);
    $result = $request->execute();
    $this->privateTempStore->set(Command::KEY_LAST_RESULT, $result->toArray());
  }

}
