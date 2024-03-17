<?php

namespace Drupal\atak\Commands;

use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 */
class BatchCommands extends DrushCommands {
  /**
   * queryInflux + trigger markerchange
   *
   * @param string $name
   *   Argument provided to the drush command.
   *
   * @command atak:go
   * @aliases atak-go
   * @options arr An option that takes multiple values.
   * @options msg Whether or not an extra message should be displayed to the user.
   * @usage atak:go 
   *   Display 'Hello Akanksha!' and a message.
   */
  public function go($name, $options = ['msg' => FALSE]) {
    if ($options['msg']) {
      $this->output()->writeln('Hello ' . $name . '! This is your first Drush 9 command.');
    }
    else {
      $this->output()->writeln('Hello ' . $name . '!');
    }
  }

}

