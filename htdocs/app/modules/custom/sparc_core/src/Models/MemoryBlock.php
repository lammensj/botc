<?php

namespace Drupal\sparc_core\Models;

class MemoryBlock {

  public function __construct(
    protected string $id,
    protected int $size,
    protected array $processes = []
  ) {
  }

  public function getId() {
    return $this->id;
  }

  public function getSize() {
    return $this->size;
  }

  public function setSize(int $size) {
    $this->size = $size;
  }

  public function getProcesses() {
    return $this->processes;
  }

  public function isUnused() {
    return empty($this->processes);
  }

  public function allocate(Process $process) {
    $this->processes[] = $process;
  }

  public function free() {
    $this->processes = [];
  }

}
