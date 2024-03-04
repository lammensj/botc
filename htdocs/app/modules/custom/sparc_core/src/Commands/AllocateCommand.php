<?php

namespace Drupal\sparc_core\Commands;

use Drupal\influxdb\Services\ClientFactory\ClientFactoryInterface;
use Drupal\sparc_core\Models\Process;
use Illuminate\Support\Collection;
use InfluxDB2\Client;
use InfluxDB2\FluxRecord;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AllocateCommand extends Command {

  protected const ALGO_FIRST_FIT = 'first_fit';
  protected const ALGO_BEST_FIT = 'best_fit';
  protected const ALGO_WORST_FIT = 'worst_fit';

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

    $this->setName('sparc:allocate');
    $this->setDescription('Allocate the appliances.');

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

    $this->addOption(
      'algorithm',
      'f',
      InputOption::VALUE_REQUIRED,
      'Defines the fit-algorithm to use.',
      self::ALGO_BEST_FIT
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

    $start = array_reduce($data, function (int $total, FluxRecord $record) {
      return $total + $record->getValue();
    }, 0);
    $output->writeln(sprintf('Total solar output: %sW', $start));

    /** @var \Drupal\sparc_core\Models\Process[] $processes */
    $processes = array_reduce($input->getOption('appliance'), function (array $carry, string $appliance) {
      preg_match('/^(?<watt>\d+)W:(?<cycles>\d+)$/', $appliance, $matches);
      $carry[] = new Process((int) $matches['watt'], (int) $matches['cycles']);

      return $carry;
    }, []);

    $this->allocateFit($data, $processes, $input->getOption('algorithm'));
    $end = array_reduce($data, function (float $total, FluxRecord $record) {
      return $total + $record->getValue();
    }, 0.0);
    $output->writeln(sprintf('Total solar output unused: %sW', $end));
    $output->writeln(sprintf('Percentage used: %s%%', (1 - ($end / $start)) * 100));

    $table = new Table($output);
    $table->setHeaders(['Start', 'Process']);
    foreach ($data as $record) {
      if (empty($record->values['processes'])) {
        continue;
      }

      foreach ($record->values['processes'] as $process) {
        $table->addRow([$record->getTime(), (string) $process]);
      }
    }
    $table->render();

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
   * @param \InfluxDB2\FluxRecord[] $records
   *   The blocks.
   * @param \Drupal\sparc_core\Models\Process[] $processes
   *   The processes.
   * @param string $algo
   *   The fit-algorithm to use.
   */
  protected function allocateFit(array &$records, array $processes, string $algo = self::ALGO_BEST_FIT) {
    $processRecords = collect($records);

    foreach ($processes as $process) {
      // Create sliding windows per process cycle, and filter them by process
      // size.
      $windows = $processRecords
        ->sliding($process->getCycles())
        ->filter(fn (Collection $window) => $window->every(fn (FluxRecord $record) => $record->getValue() >= $process->getSize()));

      if ($windows->isEmpty()) {
        // There are no windows available for the process.
        continue;
      }

      switch ($algo) {
        case self::ALGO_FIRST_FIT:
          // Get the first item, it matches the process restrictions as close
          // as possible (combination of size and cycles).
          /** @var \InfluxDB2\FluxRecord[] $window */
          $window = $windows->first();
          break;

        case self::ALGO_WORST_FIT:
          // Create a queue, the priority is the positive sum of the values.
          $queue = array_reduce($windows->toArray(), static function (\SplPriorityQueue $queue, array $window) {
            $total = array_reduce($window, static function (int $total, FluxRecord $record) {
              return $total + $record->getValue();
            }, 0);
            $queue->insert($window, $total);

            return $queue;
          }, new \SplPriorityQueue());

          // Get the item at the top, it matches the process restrictions
          // as close as possible (combination of size and cycles).
          /** @var \InfluxDB2\FluxRecord[] $window */
          $window = $queue->extract();
          break;

        default:
        case self::ALGO_BEST_FIT:
          // Create a queue, the priority is the negative sum of the blocks.
          $queue = array_reduce($windows->toArray(), static function (\SplPriorityQueue $queue, array $window) {
            $total = array_reduce($window, static function (int $total, FluxRecord $record) {
              return $total + $record->getValue();
            }, 0);
            $queue->insert($window, -$total);

            return $queue;
          }, new \SplPriorityQueue());

          // Get the item at the top, it matches the process restrictions
          // as close as possible (combination of size and cycles).
          /** @var \InfluxDB2\FluxRecord[] $window */
          $window = $queue->extract();
          break;
      }

      foreach ($window as &$record) {
        $record->values['_value'] = $record->getValue() - $process->getSize();
        $record->values['processes'][] = $process;
      }
    }
  }

}
