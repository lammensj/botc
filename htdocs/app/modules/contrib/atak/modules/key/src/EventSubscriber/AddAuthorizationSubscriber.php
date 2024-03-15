<?php

namespace Drupal\atak_key\EventSubscriber;

use Drupal\http_client_manager\Event\HttpClientEvents;
use Drupal\http_client_manager\Event\HttpClientHandlerStackEvent;
use Drupal\key\KeyInterface;
use Drupal\key\KeyRepositoryInterface;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds authorization to Atak / CiviTAK-requests.
 */
class AddAuthorizationSubscriber implements EventSubscriberInterface {

  /**
   * The API-key.
   *
   * @var \Drupal\key\KeyInterface|null
   */
  protected ?KeyInterface $key;

  /**
   * Creates an AddAuthorizationSubscriber-instance.
   *
   * @param \Drupal\key\KeyRepositoryInterface $keyRepository
   *   The key repository.
   */
  public function __construct(
    protected KeyRepositoryInterface $keyRepository
  ) {
    $this->key = $this->keyRepository->getKey('atak_api_key');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HttpClientEvents::HANDLER_STACK => 'onHandlerStack',
    ];
  }

  /**
   * Acts on the http_client.handler_stack-event.
   *
   * @param \Drupal\http_client_manager\Event\HttpClientHandlerStackEvent $event
   *   The event.
   */
  public function onHandlerStack(HttpClientHandlerStackEvent $event): void {
    if ($event->getHttpServiceApi() !== 'atak_services') {
      return;
    }

    if (empty($this->key) || empty($this->key->getKeyValue())) {
      return;
    }

    $handler = $event->getHandlerStack();
    $middleware = Middleware::mapRequest([$this, 'addAuthorization']);
    $handler->push($middleware, 'atak_services');
  }

  /**
   * Add authorization to the http-request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The http-request.
   *
   * @return \Psr\Http\Message\RequestInterface
   *   Returns the altered http-request.
   */
  public function addAuthorization(RequestInterface $request): RequestInterface {
    if ($request->hasHeader('Authorization')) {
      return $request->withHeader('Authorization', sprintf('Bearer %s', $this->key->getKeyValue()));
    }

    return $request;
  }

}
