<?php

namespace Drupal\http_client_manager\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines Guzzle RequestLocation annotation object.
 *
 * @Annotation
 */
class RequestLocation extends Plugin {

  /**
   * The ID of the location.
   *
   * @var string
   */
  public string $id;

  /**
   * The human-readable name of the request location plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public Translation $label;

}
