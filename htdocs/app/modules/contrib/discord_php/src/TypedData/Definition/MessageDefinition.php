<?php

namespace Drupal\discord_php\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Typed Data Definition resembling a Message.
 */
class MessageDefinition extends ComplexDataDefinitionBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(): array {
    if (!isset($this->propertyDefinitions)) {
      $info = &$this->propertyDefinitions;

      $info['id'] = DataDefinition::create('string')
        ->setRequired(TRUE)
        ->setLabel('ID')
        ->setDescription('The unique identifier of the message.');
      $info['channel_id'] = DataDefinition::create('string')
        ->setRequired(TRUE)
        ->setLabel('Channel ID')
        ->setDescription('The unique identifier of the channel that the message was went in.');
      $info['guild_id'] = DataDefinition::create('string')
        ->setRequired(TRUE)
        ->setLabel('Guild ID')
        ->setDescription('The unique identifier of the guild that the channel the message was sent in belongs to.');
      $info['user_id'] = DataDefinition::create('string')
        ->setRequired(TRUE)
        ->setLabel('User ID')
        ->setDescription('The user id of the author.');
      $info['content'] = DataDefinition::create('string')
        ->setLabel('Content')
        ->setDescription('The content of the message if it is a normal message.');
      $info['timestamp'] = DataDefinition::create('datetime_iso8601')
        ->setLabel('Timestamp')
        ->setDescription('A timestamp of when the message was sent.');
    }

    return $this->propertyDefinitions;
  }

}
