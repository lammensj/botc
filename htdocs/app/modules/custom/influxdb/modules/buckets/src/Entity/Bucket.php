<?php

namespace Drupal\influxdb_buckets\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\influxdb_buckets\BucketInterface;

/**
 * Defines the bucket entity type.
 *
 * @ConfigEntityType(
 *   id = "influxdb_bucket",
 *   label = @Translation("Bucket"),
 *   label_collection = @Translation("Buckets"),
 *   label_singular = @Translation("bucket"),
 *   label_plural = @Translation("buckets"),
 *   label_count = @PluralTranslation(
 *     singular = "@count bucket",
 *     plural = "@count buckets",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\influxdb_buckets\BucketListBuilder",
 *     "form" = {
 *       "add" = "Drupal\influxdb_buckets\Form\BucketForm",
 *       "edit" = "Drupal\influxdb_buckets\Form\BucketForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "influxdb_bucket",
 *   admin_permission = "administer influxdb bucket",
 *   links = {
 *     "collection" = "/admin/structure/influxdb-bucket",
 *     "add-form" = "/admin/structure/influxdb-bucket/add",
 *     "edit-form" = "/admin/structure/influxdb-bucket/{influxdb_bucket}",
 *     "delete-form" = "/admin/structure/influxdb-bucket/{influxdb_bucket}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description"
 *   }
 * )
 */
class Bucket extends ConfigEntityBase implements BucketInterface {

  /**
   * The bucket ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The bucket label.
   *
   * @var string
   */
  protected $label;

  /**
   * The bucket status.
   *
   * @var bool
   */
  protected $status;

  /**
   * The bucket description.
   *
   * @var string
   */
  protected $description;

}
