<?php

namespace Drupal\http_client_manager\EventSubscriber;

use Drupal\http_client_manager\Event\HttpClientCallPreExecuteEvent;
use Drupal\http_client_manager\Event\HttpClientEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class HttpClientManagerSubscriber.
 */
class HttpClientManagerSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HttpClientEvents::CALL_PRE_EXECUTE => ['onDataIntegrityCheck'],
    ];
  }

  /**
   * This method is called whenever the http_client.call_pre_execute event is
   * dispatched.
   *
   * @param HttpClientCallPreExecuteEvent $event
   *   The Pre Execute event.
   */
  public function onDataIntegrityCheck(HttpClientCallPreExecuteEvent $event) {
    $client = $event->client;
    $command = $event->command;
    $needs_update = FALSE;
    $data = $command->toArray();
    $params = $client->getDescription()
      ->getOperation($command->getName())
      ->getParams();
    $excluded = [
      'array',
      'object',
      'resource',
    ];

    foreach ($params as $name => $param) {
      if (!isset($data[$name]) || !isset($params[$name])) {
        continue;
      }
      $value = $data[$name];
      $type = $params[$name]->getType();
      if (empty($type) || in_array($type, $excluded) || ($type == gettype($value))) {
        continue;
      }

      $needs_update = TRUE;
      switch ($type) {
        case 'int':
        case 'integer':
          $command[$name] = (int) $value;
          break;

        case 'float':
          $command[$name] = (float) $value;
          break;

        case 'number':
          $command[$name] = preg_match('/^\d+\.\d+$/', $value) ? (float) $value : (int) $value;
          break;

        case 'string':
          $command[$name] = (string) $value;
          break;

        case 'bool':
        case 'boolean':
          $command[$name] = (bool) $value;
          break;
      }
    }

    if ($needs_update) {
      $event->command = $command;
    }
  }

}
