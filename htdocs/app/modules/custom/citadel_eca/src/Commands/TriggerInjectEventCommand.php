<?php

namespace Drupal\citadel_eca\Commands;

use Drupal\eca\Token\TokenInterface;
use Drupal\eca_base\BaseEvents;
use Drupal\eca_base\Event\CustomEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\VarDumper\VarDumper;

class TriggerInjectEventCommand extends Command {

  protected static $defaultName = 'citadel-eca:trigger:inject-event';

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
    protected EventDispatcherInterface $eventDispatcher
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
      $this->tokenService->addTokenData('custom_event:payload', $payload);
      $event->addTokenNamesFromString('custom_event.payload');
    }

    $this->eventDispatcher->dispatch($event, BaseEvents::CUSTOM);

    return 1;
  }

}
