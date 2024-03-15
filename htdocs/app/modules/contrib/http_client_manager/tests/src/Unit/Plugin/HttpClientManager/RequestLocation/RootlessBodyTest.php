<?php

namespace Drupal\Tests\http_client_manager\Unit\Plugin\HttpClientManager\RequestLocation;

use Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\RootlessBody;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Psr7\Request;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Test RootlessBody-request location.
 *
 * @coversDefaultClass \Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\RootlessBody
 * @group HttpClientManager.
 */
class RootlessBodyTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Tests Body.
   *
   * @covers ::visit
   */
  public function testVisitsLocation(): void {
    $location = new RootlessBody([], 'body', []);
    $command = new Command('payload', ['payload' => '{"message":"hello world!"}']);
    $request = new Request('POST', 'https://httbin.org');
    $param = new Parameter(['name' => 'payload']);
    $request = $location->visit($command, $request, $param);
    $this->assertEquals('{"message":"hello world!"}', $request->getBody()->getContents());
  }

}
