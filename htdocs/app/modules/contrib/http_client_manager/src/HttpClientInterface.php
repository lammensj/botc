<?php

namespace Drupal\http_client_manager;

/**
 * Interface HttpClientInterface.
 *
 * @package Drupal\http_client_manager
 */
interface HttpClientInterface {

  /**
   * Get Http Service Api data.
   *
   * @return array
   *   An array containing service api data.
   */
  public function getApi();

  /**
   * Get the configuration of the client.
   *
   * @return array
   *   The configuration of the client.
   */
  public function getClientConfig();

  /**
   * Get service api commands.
   *
   * @return mixed
   *   The service api commands.
   */
  public function getCommands();

  /**
   * Get single service api command by name.
   *
   * @param string $commandName
   *   The command name.
   *
   * @return \GuzzleHttp\Command\Guzzle\Operation
   *   The api command.
   */
  public function getCommand($commandName);

  /**
   * Execute command call.
   *
   * @param string $command_name
   *   The Guzzle command name.
   * @param array $params
   *   The Guzzle command parameters array.
   *
   * @return \GuzzleHttp\Command\ResultInterface
   *   The result of the executed command
   *
   * @throws \GuzzleHttp\Command\Exception\CommandException
   */
  public function call($command_name, array $params = []);

  /**
   * Prepare the HTTP Client for its execution.
   *
   * @param string $command_name
   *   The Guzzle command name.
   * @param array $params
   *   The Guzzle command parameters array.
   *
   * @return \Drupal\http_client_manager\LazyHttpClientInterface
   *   The Lazy Http Client instance.
   */
  public function prepare($command_name, array $params = []);

}
