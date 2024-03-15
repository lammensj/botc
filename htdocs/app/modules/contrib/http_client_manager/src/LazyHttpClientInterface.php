<?php


namespace Drupal\http_client_manager;

/**
 * Interface LazyHttpClientInterface.
 *
 * @package Drupal\http_client_manager
 */
interface LazyHttpClientInterface {

  /**
   * Execute command call.
   *
   * @return \GuzzleHttp\Command\ResultInterface
   *   The result of the executed command
   *
   * @throws \GuzzleHttp\Command\Exception\CommandException
   */
  public function execute();

  /**
   * Get client.
   *
   * @return \GuzzleHttp\Command\Guzzle\GuzzleClient
   *   The original Guzzle Client.
   */
  public function getClient();

  /**
   * Get command.
   *
   * @return \GuzzleHttp\Command\CommandInterface
   *   The Guzzle command.
   */
  public function getCommand();

}
