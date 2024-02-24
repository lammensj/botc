<?php

namespace Drupal\influxdb\Commands;

class Process implements \Stringable {

  public function __construct(
    protected int $size,
    protected int $cycles = 1
  ) {
  }

  public function getSize() {
    return $this->size;
  }

  public function getCycles() {
    return $this->cycles;
  }

  public function __toString() {
    return sprintf('%s (%s)', (string) $this->getSize(), $this->getCycles());
  }

}
