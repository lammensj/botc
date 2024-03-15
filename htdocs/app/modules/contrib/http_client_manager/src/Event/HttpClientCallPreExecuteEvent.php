<?php

namespace Drupal\http_client_manager\Event;

use Symfony\Contracts\EventDispatcher\Event;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\Guzzle\GuzzleClient;

/**
 * Class HttpClientCallPreExecuteEvent.
 *
 * @package Drupal\http_client_manager\Event
 */
class HttpClientCallPreExecuteEvent extends Event {

  /**
   * The client.
   * @var \GuzzleHttp\Command\Guzzle\GuzzleClient $client
   */
  public $client;

  /**
   * The command to be executed.
   * @var \GuzzleHttp\Command\Command $command
   */
  public $command;

  /**
   * HttpClientCallPreExecute constructor.
   *
   * @param \GuzzleHttp\Command\Guzzle\GuzzleClient $client
   *   The client to execute the command.
   * @param \GuzzleHttp\Command\Command $command
   *   The command to be executed.
   */
  public function __construct(GuzzleClient $client, Command $command) {
    $this->client = $client;
    $this->command = $command;
  }
}
