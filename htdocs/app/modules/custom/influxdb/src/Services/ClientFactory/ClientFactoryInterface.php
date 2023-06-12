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

  /**
   * Get the organization ID.
   *
   * @param string|null $name
   *   The name of the organization.
   *
   * @return string
   *   Returns the ID.
   *
   * @throws \InfluxDB2\ApiException
   */
  public function getOrganizationId(?string $name): string;

}
