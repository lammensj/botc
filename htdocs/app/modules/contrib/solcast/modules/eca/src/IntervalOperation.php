<?php

namespace Drupal\solcast_eca;

/**
 * Describes operations to perform on the interval.
 */
enum IntervalOperation: string {

  case ADD = 'add';
  case SUBTRACT = 'sub';

  /**
   * Get the operations as key-label options.
   *
   * @return array
   *   Returns an array of key-label options.
   */
  public static function options(): array {
    return array_combine(
      array_column(self::cases(), 'value'),
      array_column(self::cases(), 'name'),
    );
  }

}
