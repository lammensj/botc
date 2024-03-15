<?php

namespace Drupal\eca_base\Plugin\Action;

/**
 * Action to read value from the key value store and store the result as a token.
 *
 * @Action(
 *   id = "eca_keyvaluestore_read",
 *   label = @Translation("Key value store: read"),
 *   description = @Translation("Reads a value from the Drupal key value store by the given key. The result is stored in a token.")
 * )
 */
class KeyValueStoreRead extends KeyValueStoreBase {

  /**
   * {@inheritdoc}
   */
  function store(string $collection) {
    return $this->keyValueStoreFactory->get($collection);
  }

}
