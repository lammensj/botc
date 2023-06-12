<?php

namespace Drupal\influxdb_bucket\Services\BucketManager;

interface BucketManagerInterface {

  /**
   * Upsert a Bucket.
   *
   * @param array $data
   *   The data for the bucket.
   *
   * @return bool
   *   Returns a boolean indication upsert was successful.
   */
  public function upsertBucket(array $data): bool;

}