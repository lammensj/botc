<?php

namespace Drupal\influxdb_bucket;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Buckets.
 */
class BucketListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['label'] = $this->t('influxdb.labels.label');
    $header['id'] = $this->t('influxdb.labels.machine_name');
    $header['retention_seconds'] = $this->t('influxdb.labels.retention_seconds');
    $header['status'] = $this->t('influxdb.labels.status');
    $header['default'] = $this->t('influxdb.labels.default');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\influxdb_bucket\BucketInterface $entity */
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['retention_seconds'] = $entity->getRetentionSeconds();
    $row['status'] = $entity->status() ? $this->t('influxdb.labels.enabled') : $this->t('influxdb.labels.disabled');
    $row['default'] = $entity->isDefault() ? $this->t('influxdb.labels.default') : $this->t('influxdb.labels.not_default');

    return $row + parent::buildRow($entity);
  }

}
