<?php

namespace Drupal\http_client_manager\Plugin\Action;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\http_client_manager\HttpClientManagerFactoryInterface;
use Drupal\http_client_manager\HttpServiceApiHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deriver for http_client_manager command actions.
 */
class CommandDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The service api handler service.
   *
   * @var \Drupal\http_client_manager\HttpServiceApiHandlerInterface
   */
  protected HttpServiceApiHandlerInterface $serviceApiHandler;

  /**
   * The client manager factory.
   *
   * @var \Drupal\http_client_manager\HttpClientManagerFactoryInterface
   */
  protected HttpClientManagerFactoryInterface $clientManagerFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    $instance = new static();
    $instance->setServicesApi($container->get('http_client_manager.http_services_api'));
    $instance->setFactory($container->get('http_client_manager.factory'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    $this->derivatives = [];
    foreach ($this->serviceApiHandler->getServicesApi() as $service) {
      $client = $this->clientManagerFactory->get($service['id']);
      foreach ($client->getCommands() as $name => $command) {
        $id = implode(':', [$service['id'], $name]);
        $this->derivatives[$id] = [
          'label' => $service['title'] . ' - ' . $command->getSummary(),
          'action_entity_id' => $id,
        ] + $base_plugin_definition;
      }
    }
    return $this->derivatives;
  }

  /**
   * Set the service api handler service.
   *
   * @param \Drupal\http_client_manager\HttpServiceApiHandlerInterface $serviceApiHandler
   *   The service api handler service.
   */
  public function setServicesApi(HttpServiceApiHandlerInterface $serviceApiHandler): void {
    $this->serviceApiHandler = $serviceApiHandler;
  }

  /**
   * Set the client manager factory.
   *
   * @param \Drupal\http_client_manager\HttpClientManagerFactoryInterface $clientManagerFactory
   *   The client manager factory.
   */
  public function setFactory(HttpClientManagerFactoryInterface $clientManagerFactory): void {
    $this->clientManagerFactory = $clientManagerFactory;
  }

}
