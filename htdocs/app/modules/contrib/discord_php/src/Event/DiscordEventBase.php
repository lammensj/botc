<?php

namespace Drupal\discord_php\Event;

use Discord\Discord;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Base-class for Discord-events.
 */
abstract class DiscordEventBase extends Event {

  /**
   * Initialized a DiscordEventBase-instance.
   *
   * @param \Discord\Discord $discord
   *   The DiscordPHP-client.
   */
  public function __construct(
    protected Discord $discord
  ) {
  }

  /**
   * Get the DiscordPHP-client.
   *
   * @return \Discord\Discord
   *   Returns the DiscordPHP-client.
   */
  public function getDiscord(): Discord {
    return $this->discord;
  }

}
