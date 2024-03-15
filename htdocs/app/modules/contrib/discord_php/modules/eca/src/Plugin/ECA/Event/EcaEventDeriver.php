<?php

namespace Drupal\discord_php_eca\Plugin\ECA\Event;

use Drupal\eca\Plugin\ECA\Event\EventDeriverBase;

/**
 * Deriver for discord_php_eca event plugins.
 */
class EcaEventDeriver extends EventDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function definitions(): array {
    return EcaEvent::definitions();
  }

}
