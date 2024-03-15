<?php


namespace Drupal\http_client_manager;


use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Command\Guzzle\GuzzleClient;

class LazyHttpClient implements LazyHttpClientInterface {

  /**
   * The original Guzzle Client.
   *
   * @var GuzzleClient
   */
  protected $client;

  /**
   * The Guzzle Command.
   *
   * @var \GuzzleHttp\Command\CommandInterface
   */
  protected $command;

  /**
   * LazyHttpClient constructor.
   *
   * @param \GuzzleHttp\Command\Guzzle\GuzzleClient $client
   *   The original Guzzle Client.
   * @param \GuzzleHttp\Command\CommandInterface $command
   *   The Guzzle Command.
   */
  public function __construct(GuzzleClient $client, CommandInterface $command) {
    $this->client = $client;
    $this->command = $command;
  }

  /**
   * {@inheritDoc}
   */
  public function execute() {
    return $this->getClient()->execute($this->getCommand());
  }

  /**
   * {@inheritDoc}
   */
  public function getClient() {
    return $this->client;
  }

  /**
   * {@inheritDoc}
   */
  public function getCommand() {
    return $this->command;
  }

}
