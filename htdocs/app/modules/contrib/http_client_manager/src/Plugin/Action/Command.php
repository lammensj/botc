<?php

namespace Drupal\http_client_manager\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\http_client_manager\HttpClientManagerFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Wrapper action for all http_client_manager commands.
 *
 * @Action(
 *   id = "http_client_manager_command",
 *   deriver = "\Drupal\http_client_manager\Plugin\Action\CommandDeriver",
 *   nodocs = true
 * )
 */
class Command extends ConfigurableActionBase implements ContainerFactoryPluginInterface {

  public const KEY_LAST_RESULT = 'last result';
  public const TIMEOUT_LAST_RESULT = 5;

  /**
   * The client manager factory.
   *
   * @var \Drupal\http_client_manager\HttpClientManagerFactoryInterface
   */
  protected HttpClientManagerFactoryInterface $clientManagerFactory;

  /**
   * The private temporary key value store.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected PrivateTempStore $privateTempStore;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, HttpClientManagerFactoryInterface $clientManagerFactory, PrivateTempStore $privateTempStore) {
    $this->clientManagerFactory = $clientManagerFactory;
    $this->privateTempStore = $privateTempStore;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): Command {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client_manager.factory'),
      $container->get('tempstore.private')->get('http_client_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = [];
    [, $serviceId, $commandName] = explode(':', $this->getPluginId());
    $client = $this->clientManagerFactory->get($serviceId);
    $command = $client->getCommand($commandName);
    foreach ($command->getParams() as $id => $param) {
      if ($param->getType() !== NULL) {
        $config[$id] = $param->getDefault();
      }
    }
    return $config + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    [, $serviceId, $commandName] = explode(':', $this->getPluginId());
    $client = $this->clientManagerFactory->get($serviceId);
    $command = $client->getCommand($commandName);
    foreach ($command->getParams() as $id => $param) {
      if ($param->getType() !== NULL) {
        $form[$id] = [
          '#type' => 'textfield',
          '#title' => $param->getDescription(),
          '#default_value' => $this->configuration[$id],
          '#required' => $param->isRequired(),
        ];
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    [, $serviceId, $commandName] = explode(':', $this->getPluginId());
    $client = $this->clientManagerFactory->get($serviceId);
    $command = $client->getCommand($commandName);
    foreach ($command->getParams() as $id => $param) {
      if ($param->getType() !== NULL) {
        $this->configuration[$id] = $form_state->getValue($id);
      }
    }
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
    [, $serviceId, $commandName] = explode(':', $this->getPluginId());
    $client = $this->clientManagerFactory->get($serviceId);
    $command = $client->getCommand($commandName);
    $params = [];
    foreach ($command->getParams() as $id => $param) {
      if ($param->getType() !== NULL) {
        $params[$id] = $this->configuration[$id];
      }
    }
    $result = $client->call($commandName, $params);
    $this->privateTempStore->set(self::KEY_LAST_RESULT, $result->toArray());
  }

}
