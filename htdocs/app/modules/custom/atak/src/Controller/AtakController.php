<?php
namespace Drupal\atak\Controller;

use Drupal\Core\Controller\ControllerBase;
use InfluxDB2\Model\Dialect;
use InfluxDB2\Model\Query;
use InfluxDB2\Client;


/**
 * Provides route responses for the Example module.
 */
class AtakController extends ControllerBase {


  public function atak(){
	// Selecteer nodes die op Rood staan, en die langr dan 2 min geleden ge-update zijn.  
	// indien meer dan 2 min geleden op Rood -> plaats op groen
        $database = \Drupal::database();
        $minago = strtotime('1 minute ago');
	
	$query = $database->query("SELECT * 
		FROM {node_field_data} as da ,
			{node__field_prsnc_team} as team 
		WHERE da.nid = team.entity_id 
		AND type = 'presence'
		AND team.field_prsnc_team_value = 'Red'
		AND da.changed < " . $minago);
	$result = $query->fetchAll();
	foreach($result as $item) {
		var_dump($item);
	        $node = \Drupal\node\Entity\Node::load($item->nid);
        	$node->set('field_prsnc_team', 'Green');
		$node->save();
		var_dump($node->id());
	}
	die('KPOT');
  }

  public function markNode($uuid){
	$database = \Drupal::database();
	$query = $database->query("SELECT * FROM {node__field_prsnc_sensor_id} where field_prsnc_sensor_id_value = '" . $uuid . "'");
	$result = $query->fetchAll();
	var_dump($result[0]->entity_id);
	$node = \Drupal\node\Entity\Node::load($result[0]->entity_id);
	$node->set('field_prsnc_team', 'Red');
	$node->save(); 
  }
 

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function go() {
	  
	  
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
die('kroket');
	  return [
      '#markup' => 'Hello, world',
    ];
  }

}
