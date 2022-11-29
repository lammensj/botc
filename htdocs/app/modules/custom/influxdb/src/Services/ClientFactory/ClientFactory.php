<?php

namespace Drupal\influxdb\Services\ClientFactory;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\key\Entity\Key;
use Drupal\key\KeyRepositoryInterface;
use Http\Adapter\Guzzle6\Client as HttpClient;
use InfluxDB2\Client;

class ClientFactory implements ClientFactoryInterface {

  /**
   * Constructs a ClientFactory-instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration object factory.
   * @param \Drupal\key\KeyRepositoryInterface $keyRepository
   *   The Key repository.
   */
  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected KeyRepositoryInterface $keyRepository
  ) {}

  /**
   * {@inheritdoc}
   */
  public function createClient(string $configName, array $clientConfig = []): Client {
    $config = $this->configFactory->get($configName);

    $http = new HttpClient();

    return new Client([
      'url' => $config->get('server_url'),
      'token' => $config->get('token'),
      'httpClient' => $http,
      'allow_redirects' => $config->get('allow_redirects'),
      'debug' => $config->get('debug'),
    ]);
  }

  /**
   * Get the token from the Key-entity.
   *
   * @param string $keyId
   *   The key ID.
   *
   * @return string
   */
  protected function getToken(string $keyId): string {
    $keyEntity = $this->keyRepository->getKey($keyId);
    if ($keyEntity instanceof Key) {
      return $keyEntity->getKeyValue();
    }

    return '';
  }

}
