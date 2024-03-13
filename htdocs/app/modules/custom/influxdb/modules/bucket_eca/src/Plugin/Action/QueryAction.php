<?php

namespace Drupal\influxdb_bucket_eca\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ActionBase;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Plugin\DataType\DataTransferObject;
use InfluxDB2\Client;
use InfluxDB2\FluxRecord;
use InfluxDB2\FluxTable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Action(
 *   id = "influxdb_run_query",
 *   label = @Translation("influxdb.labels.action_run_query")
 * )
 */
class QueryAction extends ConfigurableActionBase {

  /**
   * The client.
   *
   * @var \InfluxDB2\Client
   */
  protected Client $client;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ActionBase {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->client = $container->get('influxdb.services.client_factory')
      ->createClient('influxdb.settings');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $queryApi = $this->client->createQueryApi();
    $result = $queryApi->query($this->configuration['query']);

    $rows = [];
    if (!empty($result)) {
      $records = array_reduce($result, function (array $carry, FluxTable $table) {
        return array_merge($carry, $table->records);
      }, []);
      $rows = array_reduce($records, function (array $carry, FluxRecord $record) {
        return array_merge($carry, [$record->values]);
      }, []);
    }

    $dto = DataTransferObject::create($rows);
    $this->tokenServices->addTokenData($this->configuration['eca_token_name'], $dto);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = parent::defaultConfiguration();
    $config['query'] = '';
    $config['eca_token_name'] = '';

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['query'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#title' => $this->t('influxdb.titles.query'),
      '#default_value' => $this->configuration['query'],
      '#description' => $this->t('influxdb.descriptions.query'),
    ];

    $form['eca_token_name'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('influxdb.titles.eca_token_name'),
      '#default_value' => $this->configuration['eca_token_name'],
      '#description' => $this->t('influxdb.descriptions.eca_token_name'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['query'] = $form_state->getValue('query');
    $this->configuration['eca_token_name'] = $form_state->getValue('eca_token_name');
  }

}
