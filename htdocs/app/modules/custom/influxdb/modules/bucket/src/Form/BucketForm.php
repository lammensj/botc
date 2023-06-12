<?php

namespace Drupal\influxdb_bucket\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\influxdb_bucket\Services\BucketManager\BucketManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Bucket form.
 *
 * @property \Drupal\influxdb_bucket\BucketInterface $entity
 */
class BucketForm extends EntityForm {

  /**
   * The bucket manager.
   *
   * @var \Drupal\influxdb_bucket\Services\BucketManager\BucketManagerInterface
   */
  protected BucketManagerInterface $bucketManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): BucketForm {
    $instance = parent::create($container);
    $instance->bucketManager = $container->get('influxdb_bucket.services.bucket_manager');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('influxdb.labels.label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\influxdb_bucket\Entity\Bucket::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['retention_seconds'] = [
      '#type' => 'select',
      '#title' => $this->t('influxdb.labels.retention_seconds'),
      '#options' => [
        0 => $this->t('influxdb.options.retention_seconds.never'),
        3600 => $this->t('influxdb.options.retention_seconds.1h'),
        21600 => $this->t('influxdb.options.retention_seconds.6h'),
        43200 => $this->t('influxdb.options.retention_seconds.12h'),
        86400 => $this->t('influxdb.options.retention_seconds.24h'),
        172800 => $this->t('influxdb.options.retention_seconds.48h'),
        259200 => $this->t('influxdb.options.retention_seconds.72h'),
        604800 => $this->t('influxdb.options.retention_seconds.7d'),
        1209600 => $this->t('influxdb.options.retention_seconds.14d'),
        2592000 => $this->t('influxdb.options.retention_seconds.30d'),
        7776000 => $this->t('influxdb.options.retention_seconds.90d'),
        31557600 => $this->t('influxdb.options.retention_seconds.1y'),
      ],
      '#default_value' => $this->entity->getRetentionSeconds(),
      '#required' => TRUE,
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('influxdb.labels.status'),
      '#default_value' => $this->entity->status(),
    ];

    $form['default'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('influxdb.labels.default'),
      '#default_value' => $this->entity->isDefault(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);
    $message_args = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW
      ? $this->t('influxdb.messages.bucket_created', $message_args)
      : $this->t('influxdb.messages.bucket_updated', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));

    $this->bucketManager->upsertBucket($form_state->getValues());

    return $result;
  }

}
