<?php

namespace Drupal\Tests\plugin\Unit\DefaultPluginResolver;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\plugin\DefaultPluginResolver\EventBasedDefaultPluginResolver;
use Drupal\plugin\Event\PluginEvents;
use Drupal\plugin\Event\ResolveDefaultPlugin;
use Drupal\plugin\PluginType\PluginTypeInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \Drupal\plugin\DefaultPluginResolver\EventBasedDefaultPluginResolver
 *
 * @group Plugin
 */
class EventBasedDefaultPluginResolverTest extends UnitTestCase {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $eventDispatcher;

  /**
   * The subject under test.
   *
   * @var \Drupal\plugin\DefaultPluginResolver\EventBasedDefaultPluginResolver
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

    $this->sut = new EventBasedDefaultPluginResolver($this->eventDispatcher);
  }

  /**
   * @covers ::__construct
   */
  function testConstruct() {
    $this->sut = new EventBasedDefaultPluginResolver($this->eventDispatcher);
    $this->assertInstanceOf(EventBasedDefaultPluginResolver::class, $this->sut);
  }

  /**
   * @covers ::createDefaultPluginInstance
   */
  public function testCreateDefaultPluginInstanceWithoutDefaultPluginInstance() {
    $this->eventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(ResolveDefaultPlugin::class), PluginEvents::RESOLVE_DEFAULT_PLUGIN);

    $plugin_type = $this->createMock(PluginTypeInterface::class);

    $this->assertNull($this->sut->createDefaultPluginInstance($plugin_type));
  }

  /**
   * @covers ::createDefaultPluginInstance
   */
  public function testCreateDefaultPluginInstanceWithDefaultPluginInstance() {
    $default_plugin_instance = $this->createMock(PluginInspectionInterface::class);

    $this->eventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(ResolveDefaultPlugin::class), PluginEvents::RESOLVE_DEFAULT_PLUGIN)
      ->willReturnCallback(function( ResolveDefaultPlugin $event, $event_name) use($default_plugin_instance) {
        $event->setDefaultPluginInstance($default_plugin_instance);
        return $event;
      });

    $plugin_type = $this->createMock(PluginTypeInterface::class);

    $this->assertSame($default_plugin_instance, $this->sut->createDefaultPluginInstance($plugin_type));
  }

}
