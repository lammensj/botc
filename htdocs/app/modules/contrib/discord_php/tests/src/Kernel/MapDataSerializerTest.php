<?php

namespace Drupal\Test\discord_php\Kernel;

use Carbon\Carbon;
use Discord\Discord;
use Discord\Factory\Factory;
use Discord\Http\Http;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\User;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\discord_php\TypedData\Definition\MessageDefinition;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Serializer\Serializer;

/**
 * Tests various serializations of DiscordPHP-classes.
 *
 * @group TypedData
 */
class MapDataSerializerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'discord_php',
    'key',
    'serialization',
    'system',
  ];

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\Serializer|null
   */
  protected ?Serializer $serializer;

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface|null
   */
  protected ?TypedDataManagerInterface $typedDataManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(static::$modules);

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config */
    $config = \Drupal::service('config.factory');
    $config->getEditable('system.date')
      ->set('timezone.default', 'Europe/Brussels')
      ->save();

    $this->serializer = \Drupal::service('serializer');
    $this->typedDataManager = \Drupal::typedDataManager();
  }

  /**
   * Tests whether the Message-object can be correctly serialized.
   */
  public function testMessageNormalize() {
    $now = new Carbon();
    $author = new User($this->initDiscord(), [
      'id' => uniqid(),
    ]);
    $data = [
      'id' => uniqid(),
      'channel_id' => uniqid(),
      'guild_id' => uniqid(),
      'author' => $author,
      'content' => $this->getRandomGenerator()->sentences(4),
      'timestamp' => $now,
    ];
    $message = new Message($this->initDiscord(), $data);

    $definition = MessageDefinition::create('discord_message');
    /** @var \Drupal\discord_php\Plugin\DataType\Message $typedData */
    $typedData = $this->typedDataManager->create(
      $definition,
      $this->serializer->normalize($message)
    );

    $this->assertEmpty($typedData->validate());
    $this->assertEquals(
      [
        'id',
        'channel_id',
        'guild_id',
        'user_id',
        'content',
        'timestamp',
      ],
      array_keys(json_decode($this->serializer->serialize($typedData, 'json'), TRUE))
    );
  }

  /**
   * Initialize DiscordPHP.
   *
   * @return \Discord\Discord
   *   Returns a DiscordPHP-instance.
   */
  protected function initDiscord(): Discord {
    $factory = $this->prophesize(Factory::class);
    $http = $this->prophesize(Http::class);
    $discord = $this->prophesize(Discord::class);

    $discord->getFactory()->willReturn($factory->reveal());
    $discord->getHttpClient()->willReturn($http->reveal());

    return $discord->reveal();
  }

}
