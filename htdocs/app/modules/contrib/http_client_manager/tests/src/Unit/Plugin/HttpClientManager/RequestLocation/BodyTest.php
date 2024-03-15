<?php

namespace Drupal\Tests\http_client_manager\Unit\Plugin\HttpClientManager\RequestLocation;

use Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\Body;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Psr7\Request;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Test Body-request location.
 *
 * @coversDefaultClass \Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\Body
 * @group HttpClientManager.
 */
class BodyTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Tests Body.
   *
   * @covers ::visit
   */
  public function testVisitsLocation(): void {
    $location = new Body([], 'body', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $request = new Request('POST', 'https://httbin.org');
    $param = new Parameter(['name' => 'foo']);
    $request = $location->visit($command, $request, $param);
    $this->assertEquals('foo=bar', $request->getBody()->getContents());
  }

}
