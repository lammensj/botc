<?php

namespace Drupal\influxdb_bucket_eca\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\eca\Plugin\Action\ActionBase;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Plugin\DataType\DataTransferObject;
use Drupal\eca\Service\YamlParser;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Action to create a Point.
 *
 * @Action(
 *   id = "influxdb_create_point",
 *   label = @Translation("influxdb.labels.action_create_point")
 * )
 */
class CreatePoint extends ConfigurableActionBase {

  /**
   * The yaml parser.
   *
   * @var \Drupal\eca\Service\YamlParser
   */
  protected YamlParser $yamlParser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ActionBase {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->yamlParser = $container->get('eca.service.yaml_parser');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE): bool|AccessResultInterface {
    $access = AccessResult::allowed();

    // Validate datetime_format and time.
    $format = $this->tokenServices->replaceClear($this->configuration['datetime_format']);
    $datetime = $this->tokenServices->replaceClear($this->configuration['datetime']);
    $dt = \DateTime::createFromFormat($format, $datetime);
    if ($dt === FALSE) {
      $access = AccessResult::forbidden(sprintf('Given time \'%s\' can not be parsed by given datetime format \'%s\'.', $datetime, $format));
    }

    // Validate tags and fields.
    $yamlProperties = ['tags', 'fields'];
    foreach ($yamlProperties as $property) {
      if (!empty($this->configuration[$property])) {
        try {
          $this->yamlParser->parse($this->configuration[$property]);
        }
        catch (ParseException $e) {
          $access = AccessResult::forbidden($e->getMessage());
        }
      }
    }

    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $format = $this->tokenServices->replaceClear($this->configuration['datetime_format']);
    $datetime = $this->tokenServices->replaceClear($this->configuration['datetime']);
    $dt = \DateTime::createFromFormat($format, $datetime);

    $data = [
      'name' => $this->tokenServices->replaceClear($this->configuration['name']),
      'time' => $dt->getTimestamp(),
      'precision' => $this->configuration['precision'],
    ];

    if (!empty($this->configuration['tags'])) {
      $tags = $this->yamlParser->parse($this->configuration['tags']);
      $data['tags'] = $tags;
    }

    if (!empty($this->configuration['fields'])) {
      $fields = $this->yamlParser->parse($this->configuration['fields']);
      $data['fields'] = $fields;
    }

    $point = Point::fromArray($data);
    if (!$point) {
      return;
    }

    $dto = DataTransferObject::create($data);
    $this->tokenServices->addTokenData($this->configuration['eca_token_name'], $dto);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = parent::defaultConfiguration();
    $config['name'] = '';
    $config['tags'] = '';
    $config['fields'] = '';
    $config['datetime'] = '';
    $config['datetime_format'] = 'Y-m-d\TH:i:s.u0p';
    $config['precision'] = WritePrecision::US;
    $config['eca_token_name'] = '';

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['name'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('influxdb.titles.name'),
      '#default_value' => $this->configuration['name'],
      '#description' => $this->t('influxdb.descriptions.name'),
    ];

    $form['tags'] = [
      '#type' => 'textarea',
      '#title' => $this->t('influxdb.titles.tags'),
      '#default_value' => $this->configuration['tags'],
      '#description' => $this->t('influxdb.descriptions.tags'),
    ];

    $form['fields'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#title' => $this->t('influxdb.titles.fields'),
      '#default_value' => $this->configuration['fields'],
      '#description' => $this->t('influxdb.descriptions.fields'),
    ];

    $form['datetime'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('influxdb.titles.datetime'),
      '#default_value' => $this->configuration['datetime'],
      '#description' => $this->t('influxdb.descriptions.datetime'),
    ];

    $form['datetime_format'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('influxdb.titles.datetime_format'),
      '#default_value' => $this->configuration['datetime_format'],
      '#description' => $this->t('influxdb.descriptions.datetime_format'),
    ];

    $form['precision'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('influxdb.titles.precision'),
      '#options' => array_combine(WritePrecision::getAllowableEnumValues(), WritePrecision::getAllowableEnumValues()),
      '#default_value' => $this->configuration['precision'],
      '#description' => $this->t('influxdb.descriptions.precision'),
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
    $this->configuration['name'] = $form_state->getValue('name');
    $this->configuration['tags'] = $form_state->getValue('tags');
    $this->configuration['fields'] = $form_state->getValue('fields');
    $this->configuration['datetime'] = $form_state->getValue('datetime');
    $this->configuration['datetime_format'] = $form_state->getValue('datetime_format');
    $this->configuration['precision'] = $form_state->getValue('precision');
    $this->configuration['eca_token_name'] = $form_state->getValue('eca_token_name');
  }

}
