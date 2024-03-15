<?php

namespace Drupal\Tests\http_client_manager\Unit\Plugin\HttpClientManager\RequestLocation;

use Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\Xml;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Psr7\Request;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Test Xml-request location.
 *
 * @coversDefaultClass \Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation\Xml
 * @group HttpClientManager.
 */
class XmlTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Tests Xml::visit().
   *
   * @covers ::visit
   */
  public function testVisitsLocation(): void {
    $location = new Xml([], 'xml', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $command['bar'] = 'test';
    $request = new Request('POST', 'https://httbin.org');
    $param = new Parameter(['name' => 'foo']);
    $location->visit($command, $request, $param);
    $param = new Parameter(['name' => 'bar']);
    $location->visit($command, $request, $param);
    $operation = new Operation();
    $request = $location->after($command, $request, $operation);
    $xml = $request->getBody()->getContents();

    $this->assertEquals('<?xml version="1.0"?>' . "\n"
      . '<Request><foo>bar</foo><bar>test</bar></Request>' . "\n", $xml);
    $header = $request->getHeader('Content-Type');
    $this->assertEquals([0 => 'application/xml'], $header);
  }

  /**
   * Tests Xml::after().
   *
   * @covers ::after
   */
  public function testCreatesBodyForEmptyDocument(): void {
    $location = new Xml([], 'xml', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $request = new Request('POST', 'https://httbin.org');
    $operation = new Operation([
      'data' => ['xmlAllowEmpty' => TRUE],
    ]);
    $request = $location->after($command, $request, $operation);
    $xml = $request->getBody()->getContents();
    $this->assertEquals('<?xml version="1.0"?>' . "\n"
      . '<Request/>' . "\n", $xml);

    $header = $request->getHeader('Content-Type');
    $this->assertEquals([0 => 'application/xml'], $header);
  }

  /**
   * Tests Xml::after().
   *
   * @covers ::after
   */
  public function testAddsAdditionalParameters(): void {
    $location = new Xml(['content_type' => 'test'], 'xml', []);
    $command = new Command('foo', ['foo' => 'bar']);
    $request = new Request('POST', 'https://httbin.org');
    $param = new Parameter(['name' => 'foo']);
    $command['foo'] = 'bar';
    $location->visit($command, $request, $param);
    $operation = new Operation([
      'additionalParameters' => [
        'location' => 'xml',
      ],
    ]);
    $command['bam'] = 'boo';
    $request = $location->after($command, $request, $operation);
    $xml = $request->getBody()->getContents();
    $this->assertEquals('<?xml version="1.0"?>' . "\n"
      . '<Request><foo>bar</foo><foo>bar</foo><bam>boo</bam></Request>' . "\n", $xml);
    $header = $request->getHeader('Content-Type');
    $this->assertEquals([0 => 'test'], $header);
  }

  /**
   * Tests Xml.
   *
   * @covers ::visit
   * @covers ::after
   */
  public function testAllowsXmlEncoding(): void {
    $location = new Xml([], 'xml', []);
    $operation = new Operation([
      'data' => ['xmlEncoding' => 'UTF-8'],
    ]);
    $command = new Command('foo', ['foo' => 'bar']);
    $request = new Request('POST', 'https://httbin.org');
    $param = new Parameter(['name' => 'foo']);
    $command['foo'] = 'bar';
    $location->visit($command, $request, $param);
    $request = $location->after($command, $request, $operation);
    $xml = $request->getBody()->getContents();
    $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>' . "\n"
      . '<Request><foo>bar</foo></Request>' . "\n", $xml);
  }

}
