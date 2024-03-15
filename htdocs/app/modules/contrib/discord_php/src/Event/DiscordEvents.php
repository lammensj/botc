<?php

namespace Drupal\discord_php\Event;

/**
 * Collection of events.
 */
final class DiscordEvents {

  /**
   * Name of the event fires when the client is ready.
   *
   * @Event
   *
   * @see \Drupal\discord_php\Event\ReadyEvent
   */
  const READY = 'discord_php.ready';

  /**
   * Name of the event fired when a message is created.
   *
   * @Event
   *
   * @see \Drupal\discord_php\Event\MessageCreateEvent
   */
  const MESSAGE_CREATE = 'discord_php.message.create';

}
