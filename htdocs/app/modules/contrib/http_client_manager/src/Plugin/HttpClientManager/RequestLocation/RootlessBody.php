<?php

namespace Drupal\http_client_manager\Plugin\HttpClientManager\RequestLocation;

use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;

/**
 * @RequestLocation(
 *   id = "rootless_body",
 *   translation = @Translation("Rootless body")
 * )
 */
class RootlessBody extends Body {

  /**
   * {@inheritdoc}
   */
  public function visit(CommandInterface $command, RequestInterface $request, Parameter $param): RequestInterface {
    $value = $command[$param->getName()];
    $value = $param->filter($value);

    return $request->withBody(Utils::streamFor($value));
  }

}
