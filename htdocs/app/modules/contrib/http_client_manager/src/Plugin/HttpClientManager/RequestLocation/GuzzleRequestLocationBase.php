<?php

namespace Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation;

use Drupal\http_client_manager\RequestLocation\RequestLocationBase;
use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Command\Guzzle\RequestLocation\RequestLocationInterface;
use Psr\Http\Message\RequestInterface;

/**
 * RequestLocation-base class for request locations in the Guzzle-package.
 */
abstract class GuzzleRequestLocationBase extends RequestLocationBase {

  /**
   * The RequestLocation from the Guzzle-package.
   *
   * @var \GuzzleHttp\Command\Guzzle\RequestLocation\RequestLocationInterface
   */
  protected RequestLocationInterface $location;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->location = $this->createInstance();
  }

  /**
   * {@inheritdoc}
   */
  public function visit(CommandInterface $command, RequestInterface $request, Parameter $param): RequestInterface {
    return $this->location->visit($command, $request, $param);
  }

  /**
   * {@inheritdoc}
   */
  public function after(CommandInterface $command, RequestInterface $request, Operation $operation): RequestInterface {
    return $this->location->after($command, $request, $operation);
  }

  /**
   * Create an instance of the Guzzle-package.
   *
   * @return \GuzzleHttp\Command\Guzzle\RequestLocation\RequestLocationInterface
   *   Returns an instance of the request location of the Guzzle-package.
   */
  abstract protected function createInstance(): RequestLocationInterface;

}
