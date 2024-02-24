<?php

namespace Drupal\hems_core\Commands;

use Drupal\influxdb\Commands\MemoryBlock;
use Drupal\influxdb\Commands\Process;
use Drupal\influxdb\Services\ClientFactory\ClientFactoryInterface;
use Illuminate\Support\Collection;
use InfluxDB2\Client;
use InfluxDB2\FluxRecord;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleCommand extends Command {

  /**
   * The InfluxDB-client.
   *
   * @var \InfluxDB2\Client
   */
  protected Client $client;

  /**
   * Constructs a ScheduleCommand-instance.
   *
   * @param \Drupal\influxdb\Services\ClientFactory\ClientFactoryInterface $clientFactory
   *   The client factory.
   */
  public function __construct(
    ClientFactoryInterface $clientFactory
  ) {
    parent::__construct();

    $this->client = $clientFactory->createClient('influxdb.settings');
  }

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    parent::configure();

    $this->setName('hems:schedule');
    $this->setDescription('Schedule the appliances.');

    $this->addOption(
      'window-duration',
      NULL,
      InputOption::VALUE_REQUIRED,
      'Defines the duration between the data-sample windows.',
      '15m'
    );

    $this->addOption(
      'appliance',
      'a',
      InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
      'Describes an appliance using the "Watts|#cycles"-format. Eg. "964W:16" for a dishwasher running 4h.',
      []
    );

    $this->addUsage('--window-duration=15m -a 964W:16');
    $this->addUsage('--window-duration=30m -a 964W:8');
    $this->addUsage('--window-duration=1h -a 964W:4');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $data = $this->getSolarOutput($input->getOption('window-duration'));
    if (empty($data)) {
      $output->writeln('No solar output available');

      return Command::SUCCESS;
    }

    /** @var \Drupal\influxdb\Commands\MemoryBlock[] $data */
    $data = array_reduce($data, function (array $carry, FluxRecord $record) {
      $carry[] = new MemoryBlock(uniqid(), $record->getValue());

      return $carry;
    }, []);
    $start = array_reduce($data, function (float $total, MemoryBlock $block) {
      return $total + $block->getSize();
    }, 0.0);
    $output->writeln(sprintf('Total solar output: %sW', $start));

    /** @var \Drupal\influxdb\Commands\Process[] $processes */
    $processes = array_reduce($input->getOption('appliance'), function (array $carry, string $appliance) {
      preg_match('/^(?<watt>\d+)W:(?<cycles>\d+)$/', $appliance, $matches);
      $carry[] = new Process((int) $matches['watt'], (int) $matches['cycles']);

      return $carry;
    }, []);

    $this->allocateBestFit($data, $processes);
    $end = array_reduce($data, function (float $total, MemoryBlock $block) {
      return $total + $block->getSize();
    }, 0.0);
    $output->writeln(sprintf('Total solar output unused: %sW', $end));
    $output->writeln(sprintf('Percentage used: %s%%', (1 - ($end / $start)) * 100));

    foreach ($data as $block) {
      if ($block->isUnused()) {
        continue;
      }

      $output->writeln(sprintf('Block used: %s', $block->getId()));

      foreach ($block->getProcesses() as $process) {
        $output->writeln((string) $process);
      }
    }

    return Command::SUCCESS;
  }

  /**
   * Get solar output from InfluxDB.
   *
   * @return \InfluxDB2\FluxRecord[]
   *   Returns the rows from InfluxDB.
   */
  protected function getSolarOutput(string $windowDuration): array {
    $queryApi = $this->client->createQueryApi();
    $result = $queryApi->query(sprintf('
      from(bucket: "Green Plug")
        |> range(start: now(), stop: %s)
        |> filter(fn: (r) => r._measurement == "solcast")
        |> filter(fn: (r) => r._field == "pv_estimate")
        |> aggregateWindow(every: %s, fn: mean, createEmpty: false)
        |> yield(name: "mean")
    ', '1d', $windowDuration));

    return $result ? $result[0]->records : [];
  }

  /**
   * Allocate the processes for the available blocks, using 'best fit'-algo.
   *
   * @param \Drupal\influxdb\Commands\MemoryBlock[] $blocks
   *   The blocks.
   * @param \Drupal\influxdb\Commands\Process[] $processes
   *   The processes.
   */
  protected function allocateBestFit(array &$blocks, array $processes) {
    $processBlocks = collect($blocks);

    foreach ($processes as $process) {
      // Create sliding windows per process cycle, and filter them by process
      // size.
      $windows = $processBlocks
        ->sliding($process->getCycles())
        ->filter(fn (Collection $window) => $window->every(fn (MemoryBlock $block) => $block->getSize() >= $process->getSize()));

      if ($windows->isEmpty()) {
        // There are no windows available for the process.
        continue;
      }

      // Create a queue, where the priority is the negative sum of the blocks.
      $queue = array_reduce($windows->toArray(), static function (\SplPriorityQueue $queue, array $window) {
        $total = array_reduce($window, static function (int $total, MemoryBlock $block) {
          return $total + $block->getSize();
        }, 0);
        $queue->insert($window, -$total);

        return $queue;
      }, new \SplPriorityQueue());

      // Get the item at the top, it matches the process restrictions as close
      // as possible (combination of size and cycles).
      /** @var \Drupal\influxdb\Commands\MemoryBlock[] $window */
      $window = $queue->extract();

      foreach ($window as &$block) {
        $block->allocate($process);
        $block->setSize($block->getSize() - $process->getSize());
      }
    }
  }

}
