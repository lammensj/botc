<?php

namespace Drupal\Tests\http_client_manager\Unit\Plugin\HttpClientManager\RequestLocation;

use Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\MultiPart;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Psr7\Request;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Test MultiPart-request location.
 *
 * @coversDefaultClass \Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\MultiPart
 * @group HttpClientManager.
 */
class MultiPartTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Tests MultiPart.
   *
   * @covers ::visit
   * @covers ::after
   */
  public function testVisitsLocation(): void {
    $location = new MultiPart([], 'multiPart', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $request = new Request('POST', 'https://httbin.org', []);
    $param = new Parameter(['name' => 'foo']);
    $request = $location->visit($command, $request, $param);
    $operation = new Operation();
    $request = $location->after($command, $request, $operation);
    $actual = $request->getBody()->getContents();

    $this->assertNotFalse(strpos($actual, 'name="foo"'));
    $this->assertNotFalse(strpos($actual, 'bar'));
  }

}
