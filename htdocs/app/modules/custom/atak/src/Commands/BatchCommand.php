<?php

namespace Drupal\atak\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    parent::configure();

    $this->setName('atak:batch');
    $this->setDescription('Process the batch of sensor data.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln('batch');

    return 0;
  }

}
