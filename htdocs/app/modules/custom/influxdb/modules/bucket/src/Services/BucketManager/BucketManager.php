<?php

namespace Drupal\influxdb_bucket\Services\BucketManager;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\influxdb\Services\ClientFactory\ClientFactoryInterface;
use InfluxDB2\ApiException;
use InfluxDB2\Model\BucketRetentionRules;
use InfluxDB2\Model\PatchBucketRequest;
use InfluxDB2\Model\PostBucketRequest;
use InfluxDB2\Service\BucketsService;

class BucketManager implements BucketManagerInterface {

  /**
   * The buckets service.
   *
   * @var \InfluxDB2\Service\BucketsService
   */
  protected BucketsService $bucketsService;

  /**
   * Constructs a BucketManager-instance.
   *
   * @param \Drupal\influxdb\Services\ClientFactory\ClientFactory $clientFactory
   *   The client factory.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger.
   */
  public function __construct(
    protected ClientFactoryInterface $clientFactory,
    protected LoggerChannelInterface $logger
  ) {
    $this->bucketsService = $this->clientFactory
      ->createClient('influxdb.settings')
      ->createService(BucketsService::class);
  }

  /**
   * {@inheritdoc}
   */
  public function upsertBucket(array $data): bool {
    try {
      /** @var \InfluxDB2\Model\Buckets $response */
      $response = $this->bucketsService->getBuckets(NULL, NULL, 1, NULL, NULL, NULL, $data['label']);

      if (count($response->getBuckets()) === 0) {
        $request = new PostBucketRequest();
        $request->setOrgId($this->clientFactory->getOrganizationId());
        $this->adjustUpsertRequest($request, $data);
        $this->bucketsService->postBuckets($request);

        return TRUE;
      }

      $request = new PatchBucketRequest();
      $this->adjustUpsertRequest($request, $data);
      $this->bucketsService->patchBucketsID($response->getBuckets()[0]->getId(), $request);

      return TRUE;
    }
    catch (ApiException $e) {
      $this->logger->error($e->getMessage());

      return FALSE;
    }
  }

  /**
   * Adjust the upsert request.
   *
   * @param \InfluxDB2\Model\PatchBucketRequest|\InfluxDB2\Model\PostBucketRequest $request
   *   The request.
   * @param array $data
   *   The data.
   */
  protected function adjustUpsertRequest(PatchBucketRequest|PostBucketRequest $request, array $data): void {
    $rule = new BucketRetentionRules();
    $rule->setEverySeconds((int) $data['retention_seconds']);

    $request->setName($data['label']);
    $request->setRetentionRules([$rule]);
  }

}
