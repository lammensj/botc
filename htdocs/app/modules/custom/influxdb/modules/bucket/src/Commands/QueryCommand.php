<?php

namespace Drupal\influxdb_bucket\Commands;

use Composer\Console\Input\InputOption;
use Drupal\influxdb\Services\ClientFactory\ClientFactoryInterface;
use InfluxDB2\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\VarDumper;

class QueryCommand extends Command {

  /**
   * The client.
   *
   * @var \InfluxDB2\Client
   */
  protected Client $client;

  /**
   * Constructs a QueryCommand-instance.
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

    $this->setName('influxdb:query');
    $this->setDescription('Query data');

    $this->addArgument('bucket', InputArgument::REQUIRED);
    $this->addOption('stop', NULL, InputOption::VALUE_OPTIONAL, 'End of the window', '1d');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $queryApi = $this->client->createQueryApi();
    $result = $queryApi->query(sprintf('
      from(bucket: "%s")
        |> range(start: now(), stop: %s)
        |> filter(fn: (r) => r._measurement == "solcast")
        |> filter(fn: (r) => r._field == "pv_estimate" and r._value > 0)
        |> aggregateWindow(every: 15m, fn: mean, createEmpty: false)
        |> yield(name: "mean")
    ', $input->getArgument('bucket'), $input->getOption('stop')));
    VarDumper::dump($result);

    return Command::SUCCESS;
  }

}
