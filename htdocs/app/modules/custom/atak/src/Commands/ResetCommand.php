<?php

namespace Drupal\atak\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    parent::configure();

    $this->setName('atak:reset');
    $this->setDescription('Reset the sensors');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
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

    return 0;
  }

}
