<?php

namespace Drupal\discord_php_eca\Plugin\ECA\Event;

use Drupal\discord_php\Event\DiscordEvents;
use Drupal\discord_php\Event\MessageCreateEvent;
use Drupal\discord_php\Event\ReadyEvent;
use Drupal\eca\Plugin\ECA\Event\EventBase;

/**
 * Plugin implementation of the ECA events for discord_php_eca.
 *
 * @EcaEvent(
 *   id = "discord_php_eca",
 *   deriver = "Drupal\discord_php_eca\Plugin\ECA\Event\EcaEventDeriver"
 * )
 */
class EcaEvent extends EventBase {

  /**
   * {@inheritdoc}
   */
  public static function definitions(): array {
    $definitions = [];

    $definitions['ready'] = [
      'label' => 'Ready',
      'event_name' => DiscordEvents::READY,
      'event_class' => ReadyEvent::class,
    ];

    $definitions['message_create'] = [
      'label' => 'Message: create',
      'event_name' => DiscordEvents::MESSAGE_CREATE,
      'event_class' => MessageCreateEvent::class,
    ];

    return $definitions;
  }

}
