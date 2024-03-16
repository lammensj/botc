<?php

namespace Drupal\atak\Plugin\Action;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ActionBase;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca_content\Service\EntityLoader;
use Drupal\node\NodeInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @Action(
 *   id = "atak_send_presence",
 *   label = @Translation("Send presence"),
 *   category = @Translation("Atak")
 * )
 */
class SendPresence extends ConfigurableActionBase {

  /**
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $client;

  /**
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected UuidInterface $uuid;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ActionBase {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->client = $container->get('http_client');
    $instance->uuid = $container->get('uuid');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = parent::defaultConfiguration();
    $config['entity'] = '';
    $config['team'] = 'Yellow';

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['entity'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Entity'),
      '#default_value' => $this->configuration['entity'],
      '#description' => $this->t('Provide the token name of the entity that this action should operate with.'),
    ];

    $form['team'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Team'),
      '#default_value' => $this->configuration['team'],
      '#options' => [
        'Yellow' => 'Yellow',
        'Green' => 'Green',
        'Red' => 'Red',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['entity'] = $form_state->getValue('entity');
    $this->configuration['team'] = $form_state->getValue('team');
  }

  /**
   * @inheritDoc
   */
  public function execute() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->tokenServices->getTokenData($this->configuration['entity']);

    if ($node->get('field_remote_uuid')->isEmpty()) {
      try {
        $response = $this->postPresence($node);
        $node->set('field_remote_uuid', (string) $response->getBody());
      }
      catch (ClientException $e) {
        VarDumper::dump($e);
      }

      return;
    }

    try {
      $this->putPresence($node);
    }
    catch (ClientException $e) {
      VarDumper::dump($e);
    }

    return;
  }

  protected function postPresence(NodeInterface $node): ResponseInterface {
    $payload = $this->createPayload($node);

    return $this->client->request(
      'POST',
      'http://35.206.145.140:19023/ManagePresence/postPresence',
      [
        RequestOptions::HEADERS => [
          'Authorization' => 'Bearer token',
        ],
        RequestOptions::JSON => $payload,
      ],
    );
  }

  protected function putPresence(NodeInterface $node) {
    $payload = $this->createPayload($node);
    $payload['uid'] = $node->uuid();

    $response = $this->client->request(
      'PUT',
      'http://35.206.145.140:19023/ManagePresence/putPresence',
      [
        RequestOptions::HEADERS => [
          'Authorization' => 'Bearer token',
        ],
        RequestOptions::JSON => $payload,
      ],
    );
    VarDumper::dump($response->getStatusCode());
  }

  protected function createPayload(NodeInterface $node): array {
    $geo = $node->get('field_prsnc_geolocation')->first()->getValue();

    return [
      'how' => $node->get('field_prsnc_how')->getString(),
      'latitude' => $geo['lat'],
      'longitude' => $geo['lon'],
      'name' => $node->label(),
      'role' => $node->get('field_prsnc_role')->getString(),
      'team' => $this->configuration['team'],
    ];
  }

}
