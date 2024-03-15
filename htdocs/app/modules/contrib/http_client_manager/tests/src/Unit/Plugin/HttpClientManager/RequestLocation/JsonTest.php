<?php

namespace Drupal\Tests\http_client_manager\Unit\Plugin\HttpClientManager\RequestLocation;

use Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\Json;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Psr7\Request;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Test Json-request location.
 *
 * @coversDefaultClass \Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\Json
 * @group HttpClientManager.
 */
class JsonTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Tests Json.
   *
   * @covers ::visit
   * @covers ::after
   */
  public function testVisitsLocation(): void {
    $location = new Json([], 'json', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $request = new Request('POST', 'https://httbin.org');
    $param = new Parameter(['name' => 'foo']);
    $location->visit($command, $request, $param);
    $operation = new Operation();
    $request = $location->after($command, $request, $operation);
    $this->assertEquals('{"foo":"bar"}', $request->getBody()->getContents());
    $this->assertEquals([0 => 'application/json'], $request->getHeader('Content-Type'));
  }

  /**
   * Tests Json.
   *
   * @covers ::visit
   * @covers ::after
   */
  public function testVisitsAdditionalProperties(): void {
    $location = new Json(['content_type' => 'foo'], 'json', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $command['baz'] = ['bam' => [1]];
    $request = new Request('POST', 'https://httbin.org');
    $param = new Parameter(['name' => 'foo']);
    $location->visit($command, $request, $param);
    $operation = new Operation([
      'additionalParameters' => [
        'location' => 'json',
      ],
    ]);
    $request = $location->after($command, $request, $operation);
    $this->assertEquals('{"foo":"bar","baz":{"bam":[1]}}', $request->getBody()->getContents());
    $this->assertEquals([0 => 'foo'], $request->getHeader('Content-Type'));
  }

  /**
   * Tests Json.
   *
   * @covers ::visit
   * @covers ::after
   */
  public function testVisitsNestedLocation(): void {
    $location = new Json([], 'json', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $request = new Request('POST', 'https://httbin.org');
    $param = new Parameter([
      'name' => 'foo',
      'type' => 'object',
      'properties' => [
        'baz' => [
          'type' => 'array',
          'items' => [
            'type' => 'string',
            'filters' => ['strtoupper'],
          ],
        ],
      ],
      'additionalProperties' => [
        'type' => 'array',
        'items' => [
          'type' => 'string',
          'filters' => ['strtolower'],
        ],
      ],
    ]);
    $command['foo'] = [
      'baz' => ['a', 'b'],
      'bam' => ['A', 'B'],
    ];
    $location->visit($command, $request, $param);
    $operation = new Operation();
    $request = $location->after($command, $request, $operation);
    $this->assertEquals('{"foo":{"baz":["A","B"],"bam":["a","b"]}}', (string) $request->getBody()->getContents());
    $this->assertEquals([0 => 'application/json'], $request->getHeader('Content-Type'));
  }

}
