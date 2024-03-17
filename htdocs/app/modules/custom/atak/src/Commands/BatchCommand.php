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
    # You can generate a Token from the "Tokens Tab" in the UI
    $token = '2dSsqJleem3M3Fw';
    $org = '2ef73e595ae16731';
    $bucket = 'botc';

    # Next, we will instantiate the client and establish a connection
    $client = new \InfluxDB2\Client([
      "url" => "http://35.206.145.140:8086", // url and port of your instance
      "token" => $token,
      "bucket" => $bucket,
      "org" => $org,
    ]);
    $queryApi = $client->createQueryApi();
    $query = "from(bucket: \"botc\")
      |> range(start: -5s)
      |> group(columns: [\"UUID\", \"_field\"])
      |> filter(fn: (r) => r[\"_measurement\"] == \"TEST2\")
      |> aggregateWindow(every: 1s, fn: mean, createEmpty: false)
      |> yield(name: \"mean\")
    ";

    $tables = $queryApi->query($query, $org);
    foreach($tables as $table) {
      foreach($table->records as $record) {
        //var_dump($record);
        $uuid = $record->values['UUID'];
        $field = $record->getField();
        $value = $record->getValue();

        if($record->values['_field'] == 'MIC' ) {
          // test audio threshold
          if($value > 80){
            $this->markNode($uuid);
          }
        }
        if($record->values['_field'] == 'VOC' ) {
          // test audio threshold
          if($value > 80) {
            $this->markNode($uuid);
          }
        }
        if($record->values['_field'] == 'SEISMIC' ) {
          // test audio threshold
          if($value > 80) {
            $this->markNode($uuid);
          }
        }
        if($record->values['_field'] == 'RF_RSSI' ) {
          // test audio threshold
          //
          if($value > 80) {
            $this->markNode($uuid);
          }
        }
        if($record->values['_field'] == 'TEMP' ) {
          // test audio threshold
          if($value > 80) {
            $this->markNode($uuid);
          }
        }
      }
    }

    return 0;
  }

  protected function markNode($uuid){
    $database = \Drupal::database();
    $query = $database->query("SELECT * FROM {node__field_prsnc_sensor_id} where field_prsnc_sensor_id_value = '" . $uuid . "'");
    $result = $query->fetchAll();
    var_dump($result[0]->entity_id);
    $node = \Drupal\node\Entity\Node::load($result[0]->entity_id);
    $node->set('field_prsnc_team', 'Red');
    $node->save();
  }

}
