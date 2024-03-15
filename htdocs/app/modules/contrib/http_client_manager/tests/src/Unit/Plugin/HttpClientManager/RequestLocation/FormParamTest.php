<?php

namespace Drupal\Tests\http_client_manager\Unit\Plugin\HttpClientManager\RequestLocation;

use Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\FormParam;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Psr7\Request;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Test FormParam-request location.
 *
 * @coversDefaultClass \Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\FormParam
 * @group HttpClientManager.
 */
class FormParamTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Tests FormParam.
   *
   * @covers ::visit
   * @covers ::after
   */
  public function testVisitsLocation(): void {
    $location = new FormParam([], 'formParam', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $request = new Request('POST', 'https://httbin.org');
    $param = new Parameter(['name' => 'foo']);
    $request = $location->visit($command, $request, $param);
    $operation = new Operation();
    $request = $location->after($command, $request, $operation);
    $this->assertEquals('foo=bar', $request->getBody()->getContents());
    $this->assertEquals([0 => 'application/x-www-form-urlencoded; charset=utf-8'], $request->getHeader('Content-Type'));
  }

  /**
   * Tests FormParam.
   *
   * @covers ::visit
   * @covers ::after
   */
  public function testAddsAdditionalProperties(): void {
    $location = new FormParam([], 'formParam', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $command['add'] = 'props';
    $request = new Request('POST', 'https://httbin.org', []);
    $param = new Parameter(['name' => 'foo']);
    $request = $location->visit($command, $request, $param);
    $operation = new Operation([
      'additionalParameters' => [
        'location' => 'formParam',
      ],
    ]);
    $request = $location->after($command, $request, $operation);
    $this->assertEquals('foo=bar&add=props', $request->getBody()->getContents());
  }

}
