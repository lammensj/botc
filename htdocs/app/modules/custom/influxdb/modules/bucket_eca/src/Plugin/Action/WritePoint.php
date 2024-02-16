<?php

namespace Drupal\influxdb_bucket_eca\Plugin\Action;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\eca\EcaState;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Token\TokenInterface;
use Drupal\influxdb\Services\ClientFactory\ClientFactoryInterface;
use Drupal\influxdb_bucket\BucketInterface;
use InfluxDB2\Point;
use InfluxDB2\Service\BucketsService;
use InfluxDB2\Writer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Action to write a point.
 *
 * @Action(
 *   id = "influxdb_write_point",
 *   label = @Translation("influxdb.labels.action_write_point")
 * )
 */
class WritePoint extends ConfigurableActionBase implements ContainerFactoryPluginInterface {

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
   */
  protected NormalizerInterface $serializer;

  /**
   * The Buckets-service.
   *
   * @var \InfluxDB2\Service\BucketsService
   */
  protected BucketsService $bucketsService;

  /**
   * The Writer-service.
   *
   * @var \InfluxDB2\Writer
   */
  protected Writer $writerApi;

  /**
   * {@inheritdoc}
   */
  final public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    TokenInterface $token_services,
    AccountProxyInterface $current_user,
    TimeInterface $time,
    EcaState $state,
    protected ClientFactoryInterface $clientFactory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $token_services, $current_user, $time, $state);

    $client = $this->clientFactory->createClient('influxdb.settings');
    $this->writerApi = $client->createWriteApi();
    $this->bucketsService = $client->createService(BucketsService::class);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): WritePoint {
    $instance = new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('eca.token_services'),
      $container->get('current_user'),
      $container->get('datetime.time'),
      $container->get('eca.state'),
      $container->get('influxdb.services.client_factory')
    );
    $instance->serializer = $container->get('serializer');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE): bool|AccessResultInterface {
    $access = AccessResult::allowed();

    if (!$this->tokenServices->hasTokenData($this->configuration['eca_token_name'])) {
      $access = AccessResult::forbidden(sprintf('Token-data for token \'%s\' not set.', $this->configuration['eca_token_name']));
    }

    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $data = $this->tokenServices->getTokenData($this->configuration['eca_token_name']);
    $data = (array) $this->serializer->normalize($data);
    $point = Point::fromArray($data);

    /** @var \Drupal\influxdb_bucket\BucketInterface $config */
    $config = $this->entityTypeManager->getStorage('influxdb_bucket')
      ->load($this->configuration['bucket']);
    $buckets = $this->bucketsService->getBuckets(NULL, NULL, 1, NULL, NULL, NULL, $config->label());

    $this->writerApi->write($point, $point->getPrecision(), $buckets->getBuckets()[0]->getId());
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = parent::defaultConfiguration();
    $config['eca_token_name'] = '';
    $config['bucket'] = '';

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['eca_token_name'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('influxdb.titles.eca_token_name'),
      '#default_value' => $this->configuration['eca_token_name'],
      '#description' => $this->t('influxdb.descriptions.eca_token_name'),
    ];

    $options = array_reduce($this->entityTypeManager->getStorage('influxdb_bucket')->loadMultiple(), function (array $carry, BucketInterface $bucket) {
      $carry[$bucket->id()] = $bucket->label();

      return $carry;
    }, []);

    $form['bucket'] = [
      '#type' => 'select',
      '#options' => $options,
      '#required' => TRUE,
      '#title' => $this->t('influxdb.labels.bucket'),
      '#default_value' => $this->configuration['bucket'],
      '#description' => $this->t('influxdb.descriptions.bucket'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['eca_token_name'] = $form_state->getValue('eca_token_name');
    $this->configuration['bucket'] = $form_state->getValue('bucket');
  }

}
