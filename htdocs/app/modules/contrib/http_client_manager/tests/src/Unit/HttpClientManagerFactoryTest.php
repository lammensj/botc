<?php

namespace Drupal\Tests\http_client_manager\Unit;

use Drupal\http_client_manager\RequestLocation\RequestLocationPluginManager;
use Prophecy\PhpUnit\ProphecyTrait;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\http_client_manager\HttpServiceApiHandlerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\http_client_manager\HttpClientManagerFactory;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class HttpClientManagerFactoryTest.
 *
 * @package Drupal\Tests\http_client_manager\Unit
 * @coversDefaultClass \Drupal\http_client_manager\HttpClientManagerFactory
 * @group HttpClientManager
 */
class HttpClientManagerFactoryTest extends UnitTestCase {

  use ProphecyTrait;
  /**
   * Tests HttpClientManagerFactory::get().
   *
   * @covers ::get
   */
  public function testGet() {
    $factory = new HttpClientManagerFactory();
    $container = new ContainerBuilder();

    $apiHandler = $this->prophesize(HttpServiceApiHandlerInterface::class);
    $apiHandler->load(Argument::any())->will(function ($args) {
      return $args;
    });
    $container->set('http_client_manager.http_services_api', $apiHandler->reveal());

    $event_dispatcher = $this->prophesize(EventDispatcherInterface::class);
    $container->set('event_dispatcher', $event_dispatcher->reveal());

    $request_location_plugin_manager = $this->prophesize(RequestLocationPluginManager::class);
    $container->set('plugin.manager.http_client_manager.request_location', $request_location_plugin_manager->reveal());

    $factory->setContainer($container);

    // Ensure that when called with the same argument, always the same instance
    // will be returned.
    $this->assertSame($factory->get('test'), $factory->get('test'));
  }

}
