<?php

namespace Drupal\influxdb_bucket\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\influxdb_bucket\BucketInterface;

/**
 * Defines the Bucket entity type.
 *
 * @ConfigEntityType(
 *   id = "influxdb_bucket",
 *   label = @Translation("influxdb.titles.bucket"),
 *   label_collection = @Translation("influxdb.titles.buckets"),
 *   label_singular = @Translation("influxdb.titles.bucket_singular"),
 *   label_plural = @Translation("influxdb.titles.bucket_plural"),
 *   label_count = @PluralTranslation(
 *     singular = "influxdb.titles.bucket_count_singular",
 *     plural = "influxdb.titles.bucket_count_plural",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\influxdb_bucket\BucketListBuilder",
 *     "form" = {
 *       "add" = "Drupal\influxdb_bucket\Form\BucketForm",
 *       "edit" = "Drupal\influxdb_bucket\Form\BucketForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "influxdb_bucket",
 *   admin_permission = "administer influxdb bucket",
 *   links = {
 *     "collection" = "/admin/config/services/influxdb/buckets",
 *     "add-form" = "/admin/config/services/influxdb/buckets/add",
 *     "edit-form" = "/admin/config/services/influxdb/buckets/{influxdb_bucket}",
 *     "delete-form" = "/admin/config/services/influxdb/buckets/{influxdb_bucket}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "retention_seconds",
 *     "status",
 *     "default"
 *   }
 * )
 */
class Bucket extends ConfigEntityBase implements BucketInterface {

  /**
   * The bucket ID.
   *
   * @var string
   */
  protected string $id;

  /**
   * The bucket label.
   *
   * @var string
   */
  protected string $label;

  /**
   * The number of retention seconds.
   *
   * @var int
   */
  protected int $retention_seconds = 0;

  /**
   * The bucket status.
   *
   * @var bool
   */
  protected $status;

  /**
   * A boolean indicating whether the Bucket is the default one.
   *
   * @var bool
   */
  protected bool $default = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getRetentionSeconds(): int {
    return $this->retention_seconds;
  }

  /**
   * {@inheritdoc}
   */
  public function isDefault(): bool {
    return $this->default;
  }

}
