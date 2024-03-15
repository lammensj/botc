<?php

namespace Drupal\Tests\http_client_manager\Unit\Plugin\HttpClientManager\RequestLocation;

use Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\Header;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Psr7\Request;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Test Header-request location.
 *
 * @coversDefaultClass \Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\Header
 * @group HttpClientManager.
 */
class HeaderTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Tests Header::visit().
   *
   * @covers ::visit
   */
  public function testVisitsLocation(): void {
    $location = new Header([], 'header', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $request = new Request('POST', 'https://httbin.org');
    $param = new Parameter(['name' => 'foo']);
    $request = $location->visit($command, $request, $param);

    $header = $request->getHeader('foo');
    $this->assertIsArray($header);
    $this->assertEquals([0 => 'bar'], $request->getHeader('foo'));
  }

  /**
   * Tests Header::after().
   *
   * @covers ::after
   */
  public function testAddsAdditionalProperties(): void {
    $location = new Header([], 'header', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $command['add'] = 'props';
    $operation = new Operation([
      'additionalParameters' => [
        'location' => 'header',
      ],
    ]);
    $request = new Request('POST', 'https://httbin.org');
    $request = $location->after($command, $request, $operation);

    $header = $request->getHeader('add');
    $this->assertIsArray($header);
    $this->assertEquals([0 => 'props'], $header);
  }

}
