<?php

namespace Drupal\eca_base\Plugin\Action;

/**
 * Action to read value from the shared temp store and store the result as a token.
 *
 * @Action(
 *   id = "eca_sharedtempstore_read",
 *   label = @Translation("Shared temporary store: read"),
 *   description = @Translation("Reads a value from the Drupal shared temporary store by the given key. The result is stored in a token.")
 * )
 */
class SharedTempStoreRead extends KeyValueStoreBase {

  /**
   * {@inheritdoc}
   */
  function store(string $collection) {
    return $this->sharedTempStoreFactory->get($collection);
  }

}
