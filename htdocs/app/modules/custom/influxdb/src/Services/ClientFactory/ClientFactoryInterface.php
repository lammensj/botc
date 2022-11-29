<?php

namespace Drupal\influxdb\Services\ClientFactory;

use InfluxDB2\Client;

interface ClientFactoryInterface {

  /**
   * Creates an InfluxDB2-client.
   *
   * @param string $configName
   *   The name of the configuration object to use.
   * @param array $clientConfig
   *   Additional configuration settings.
   *
   * @return \InfluxDB2\Client
   *   Return a configured InfluxDB2-client.
   */
  public function createClient(string $configName, array $clientConfig = []): Client;

}
