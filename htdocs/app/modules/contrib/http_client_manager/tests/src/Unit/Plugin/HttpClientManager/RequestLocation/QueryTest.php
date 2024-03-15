<?php

namespace Drupal\Tests\http_client_manager\Unit\Plugin\HttpClientManager\RequestLocation;

use Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\Query;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Psr7\Query as GuzzleQuery;
use GuzzleHttp\Psr7\Request;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Test Query-request location.
 *
 * @coversDefaultClass \Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\Query
 * @group HttpClientManager.
 */
class QueryTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Tests Query::visit().
   *
   * @covers ::visit
   */
  public function testVisitsLocation(): void {
    $location = new Query([], 'query', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $request = new Request('POST', 'https://httbin.org');
    $param = new Parameter(['name' => 'foo']);
    $request = $location->visit($command, $request, $param);

    $this->assertEquals('foo=bar', urldecode($request->getUri()->getQuery()));
  }

  /**
   * Tests Query::visit().
   *
   * @covers ::visit
   */
  public function testVisitsMultipleLocations(): void {
    $request = new Request('POST', 'https://httbin.org');

    // First location.
    $location = new Query([], 'query', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $param = new Parameter(['name' => 'foo']);
    $request = $location->visit($command, $request, $param);

    // Second location.
    $location = new Query([], 'query', []);
    $command = new Command('baz', ['baz' => [6, 7]]);
    $param = new Parameter(['name' => 'baz']);
    $request = $location->visit($command, $request, $param);

    $this->assertEquals('foo=bar&baz[0]=6&baz[1]=7', urldecode($request->getUri()->getQuery()));
  }

  /**
   * Tests Query::after().
   *
   * @covers ::after
   */
  public function testAddsAdditionalProperties(): void {
    $location = new Query([], 'query', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $command['add'] = 'props';
    $operation = new Operation([
      'additionalParameters' => [
        'location' => 'query',
      ],
    ]);
    $request = new Request('POST', 'https://httbin.org');
    $request = $location->after($command, $request, $operation);

    $this->assertEquals('props', GuzzleQuery::parse($request->getUri()->getQuery())['add']);
  }

}
