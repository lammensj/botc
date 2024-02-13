<?php

namespace Drupal\influxdb\Services\ClientFactory;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\key\Entity\Key;
use Drupal\key\KeyRepositoryInterface;
use Http\Adapter\Guzzle7\Client as HttpClient;
use InfluxDB2\Client;
use InfluxDB2\Service\OrganizationsService;

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
      'org' => $config->get('organization'),
      'token' => $this->getToken((string) $config->get('token')),
      'httpClient' => $http,
      'allow_redirects' => $config->get('allow_redirects'),
      'debug' => $config->get('debug'),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getOrganizationId(?string $name = NULL): string {
    /** @var \InfluxDB2\Service\OrganizationsService $orgService */
    $orgService = $this->createClient('influxdb.settings')
      ->createService(OrganizationsService::class);

    if (empty($name)) {
      $config = $this->configFactory->get('influxdb.settings');
      $name = $config->get('organization');
    }

    $response = $orgService->getOrgs(NULL, NULL, 1, FALSE, $name);

    return $response->getOrgs()[0]->getId();
  }

  /**
   * Get the token from the Key-entity.
   *
   * @param string $keyId
   *   The key ID.
   *
   * @return string
   *   Returns the auth-token.
   */
  protected function getToken(string $keyId): string {
    $keyEntity = $this->keyRepository->getKey($keyId);
    if ($keyEntity instanceof Key) {
      return $keyEntity->getKeyValue();
    }

    return '';
  }

}
