<?php

namespace Drupal\discord_php\Drush\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\discord_php\Event\DiscordEvents;
use Drupal\discord_php\Event\MessageCreateEvent;
use Drupal\discord_php\Event\ReadyEvent;
use Drupal\discord_php\Services\DiscordPhpManager\DiscordPhpManagerInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Commands for Discord.
 */
final class DiscordPhpCommands extends DrushCommands implements ContainerInjectionInterface {

  /**
   * Constructs a DiscordPhpCommands object.
   */
  final public function __construct(
    protected readonly DiscordPhpManagerInterface $discordPhpManager,
    protected readonly EventDispatcherInterface $dispatcher
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): DiscordPhpCommands {
    return new self(
      $container->get('discord_php.services.discord_php_manager'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * Run the DiscordPHP-loop.
   */
  #[CLI\Command(name: 'discord-php:run')]
  #[CLI\Option(name: 'timeout', description: 'The amount of seconds to run the loop.')]
  public function run($options = ['timeout' => 0]): void {
    $discord = $this->discordPhpManager->getClient();
    if (!$discord) {
      $this->logger()->error('Could not initialize DiscordPHP-client.');

      return;
    }

    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
      $event = new MessageCreateEvent($discord, $message);
      $this->dispatcher->dispatch($event, DiscordEvents::MESSAGE_CREATE);
    });

    $discord->on('ready', function (Discord $discord) use ($options) {
      $event = new ReadyEvent($discord);
      $this->dispatcher->dispatch($event, DiscordEvents::READY);

      if ($options['timeout'] > 0) {
        $loop = $discord->getLoop();
        $loop->addTimer($options['timeout'], function () use ($discord) {
          $discord->close();
        });
      }
    });

    $discord->run();
  }

}
