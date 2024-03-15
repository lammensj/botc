<?php

namespace Drupal\discord_php\Event;

use Discord\Discord;
use Discord\Parts\Channel\Message;

/**
 * Payload that's part of the 'discord_php.message.create'-event.
 */
class MessageCreateEvent extends DiscordEventBase {

  /**
   * Constructs a new MessageCreateEvent.
   *
   * @param \Discord\Discord $discord
   *   The DiscordPHP-client.
   * @param \Discord\Parts\Channel\Message $message
   *   The message.
   */
  public function __construct(
    protected Discord $discord,
    protected Message $message
  ) {
    parent::__construct($discord);
  }

  /**
   * Get the message.
   *
   * @return \Discord\Parts\Channel\Message
   *   Returns the message.
   */
  public function getMessage(): Message {
    return $this->message;
  }

}
