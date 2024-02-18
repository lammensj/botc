<?php

namespace Drupal\hems\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    parent::configure();

    $this->setName('hems:schedule');
    $this->setDescription('Schedule the appliances.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    parent::execute($input, $output);

    $output->writeln('foo');

    return Command::SUCCESS;
  }

}
