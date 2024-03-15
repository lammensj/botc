<?php

namespace Drupal\Tests\plugin\Unit;
use PHPUnit\Framework\Assert;
use Drupal\Core\Url;

/**
 * Provides assertions to test operations links integrity.
 */
trait OperationsProviderTestTrait {

  /**
   * Checks the integrity of operations links.
   *
   * @param mixed[] $operations_links
   */
  protected function assertOperationsLinks(array $operations_links) {
    foreach ($operations_links as $link) {
      Assert::assertArrayHasKey('title', $link);
      Assert::assertNotEmpty($link['title']);

      Assert::assertArrayHasKey('url', $link);
      Assert::assertInstanceOf(Url::class, $link['url']);
    }
  }

}
