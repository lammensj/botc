<?php

namespace Drupal\solcast;

use GuzzleHttp\TransferStats;

/**
 * Helper-class for http_services_api.
 */
class HttpServicesApiHelper {

  /**
   * Log the stats of the request.
   *
   * @param \GuzzleHttp\TransferStats $stats
   *   The stats.
   */
  public static function onStats(TransferStats $stats): void {
    \Drupal::logger('solcast')->debug(print_r($stats, TRUE));
  }

}
