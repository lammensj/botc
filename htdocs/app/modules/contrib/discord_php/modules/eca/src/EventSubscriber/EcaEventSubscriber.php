<?php

namespace Drupal\discord_php_eca\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\Core\Utility\Error;
use Drupal\discord_php\Event\MessageCreateEvent;
use Drupal\discord_php\TypedData\Definition\MessageDefinition;
use Drupal\discord_php_eca\Plugin\ECA\Event\EcaEvent;
use Drupal\eca\EcaEvents;
use Drupal\eca\Event\BeforeInitialExecutionEvent;
use Drupal\eca\EventSubscriber\EcaBase;
use Drupal\eca\Processor;
use Drupal\eca\Token\TokenInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Event subscriber for discord_php_eca.
 */
class EcaEventSubscriber extends EcaBase {

  /**
   * Constructs an EcaEventSubscriber instance.
   *
   * @param \Drupal\eca\Processor $processor
   *   The processor.
   * @param \Drupal\eca\Token\TokenInterface $token_service
   *   The token service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger.
   * @param \Symfony\Component\Serializer\Normalizer\NormalizerInterface $serializer
   *   The serializer.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed data manager.
   */
  public function __construct(
    Processor $processor,
    TokenInterface $token_service,
    protected LoggerChannelInterface $logger,
    protected NormalizerInterface $serializer,
    protected TypedDataManagerInterface $typedDataManager
  ) {
    parent::__construct($processor, $token_service);
  }

  /**
   * Before the execution, find the events to intercept.
   *
   * @param \Drupal\eca\Event\BeforeInitialExecutionEvent $beforeEvent
   *   The event.
   */
  public function onBeforeInitialExecution(BeforeInitialExecutionEvent $beforeEvent): void {
    $event = $beforeEvent->getEvent();

    if ($event instanceof MessageCreateEvent) {
      $definition = MessageDefinition::create('discord_message');
      $data = [];
      try {
        $data = $this->typedDataManager->create(
          $definition,
          $this->serializer->normalize($event->getMessage())
        );
      }
      catch (ExceptionInterface $e) {
        $this->logger->error(Error::DEFAULT_ERROR_MESSAGE, Error::decodeException($e));
      }

      $this->tokenService->addTokenData('message', $data);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = array_reduce(EcaEvent::definitions(), static function ($events, $definition) {
      $events[$definition['event_name']][] = ['onEvent'];

      return $events;
    }, []);

    $events[EcaEvents::BEFORE_INITIAL_EXECUTION][] = [
      'onBeforeInitialExecution',
      -100,
    ];

    return $events;
  }

}
