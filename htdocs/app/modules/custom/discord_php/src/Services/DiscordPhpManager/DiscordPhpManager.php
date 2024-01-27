<?php

namespace Drupal\discord_php\Services\DiscordPhpManager;

use Discord\Discord;
use Discord\Exceptions\IntentException;
use Discord\WebSockets\Intents;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Utility\Error;
use Drupal\key\KeyRepositoryInterface;

/**
 * The DiscordPHP-manager.
 */
class DiscordPhpManager implements DiscordPhpManagerInterface {

  /**
   * The DiscordPHP-client.
   *
   * @var \Discord\Discord|null
   */
  protected ?Discord $client = NULL;

  /**
   * Initializes a DiscordPhpManager-instance.
   *
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger.
   * @param \Drupal\key\KeyRepositoryInterface $keyRepository
   *   The key-repository.
   */
  public function __construct(
    protected readonly LoggerChannelInterface $logger,
    protected readonly KeyRepositoryInterface $keyRepository,
  ) {
    $this->initClient();
  }

  /**
   * {@inheritdoc}
   */
  public function getClient(): ?Discord {
    return $this->client;
  }

  /**
   * Initialize the DiscordPHP-client.
   */
  protected function initClient(): void {
    $key = $this->keyRepository->getKey('discord_php_token');
    if (empty($key) || empty($key->getKeyValue())) {
      $this->logger->error('Could not initialize DiscordPHP: key "discord_php_token" was empty.');

      return;
    }

    try {
      $this->client = new Discord([
        'token' => $key->getKeyValue(),
        'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT,
        'logger' => $this->logger,
      ]);

      return;
    }
    catch (IntentException $e) {
      $this->logger->error(Error::DEFAULT_ERROR_MESSAGE, Error::decodeException($e));
    }
  }

}
