<?php

namespace Drupal\discord_php\Plugin\DataType;

use Drupal\Core\TypedData\Plugin\DataType\Map;

/**
 * Data type for Message.
 *
 * @DataType(
 *   id = "discord_message",
 *   label = @Translation("Message"),
 *   definition_class = "\Drupal\discord_php\TypedData\Definition\MessageDefinition"
 * )
 */
class Message extends Map {

}
