<?php

namespace Drupal\discord_php\Normalizer;

use Discord\Parts\Channel\Message;
use Drupal\serialization\Normalizer\NormalizerBase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Supports normalization of Message-class.
 */
class MessageNormalizer extends NormalizerBase implements NormalizerInterface {

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []): array|string|int|float|bool|\ArrayObject|null {
    /** @var \Discord\Parts\Channel\Message $object */
    $data = [];
    $data['id'] = $object->id;
    $data['channel_id'] = $object->channel_id;
    $data['guild_id'] = $object->guild_id;
    $data['user_id'] = $object->user_id;
    $data['content'] = $object->content;
    $data['timestamp'] = $object->timestamp->format(\DateTimeInterface::RFC3339);

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedTypes(?string $format): array {
    return [
      Message::class => TRUE,
    ];
  }

}
