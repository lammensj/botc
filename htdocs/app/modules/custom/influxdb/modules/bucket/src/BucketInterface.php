<?php

namespace Drupal\influxdb_bucket;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a bucket entity type.
 */
interface BucketInterface extends ConfigEntityInterface {

  /**
   * Get the number of retention seconds.
   *
   * @return int
   */
  public function getRetentionSeconds(): int;

  /**
   * Get whether the Bucket is marked as 'default'.
   *
   * @return bool
   */
  public function isDefault(): bool;

}
