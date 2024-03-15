<?php

namespace Drupal\discord_php\Services\DiscordPhpManager;

use Discord\Discord;

/**
 * The DiscordPHP-manager.
 */
interface DiscordPhpManagerInterface {

  /**
   * Get the client.
   *
   * @return \Discord\Discord|null
   *   Returns the initialized DiscordPHP-client or NULL if init failed.
   */
  public function getClient(): ?Discord;

}
