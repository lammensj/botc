<?php

namespace Drupal\sparc_Core\Commands;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\eca\Token\TokenInterface;
use Drupal\eca_base\BaseEvents;
use Drupal\eca_base\Event\CustomEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TriggerInjectEventCommand extends Command {

  protected static $defaultName = 'sparc:trigger:inject-event';

  /**
   * Constructs a TriggerInjectEventCommand-instance.
   *
   * @param \Drupal\eca\Token\TokenInterface $tokenService
   *   The token service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   */
  public function __construct(
    protected TokenInterface $tokenService,
    protected EventDispatcherInterface $eventDispatcher,
    protected TypedDataManagerInterface $dataManager,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    parent::configure();

    $this->addArgument('id', InputArgument::REQUIRED, 'The id of the custom event to be triggered.');
    $this->addOption('payload', NULL, InputOption::VALUE_OPTIONAL, 'An optional payload to inject.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $event = new CustomEvent($input->getArgument('id'));

    $payload = $input->getOption('payload');
    if (!empty($payload)) {
      $definition = MapDataDefinition::create()
        ->setPropertyDefinition('payload', DataDefinition::create('string'));
      $data = $this->dataManager->create($definition, [
        'payload' => $payload,
      ]);

      $this->tokenService->addTokenData('custom_event', $data);
      $event->addTokenNamesFromString('custom_event');
    }

    $this->eventDispatcher->dispatch($event, BaseEvents::CUSTOM);

    return 1;
  }

}
