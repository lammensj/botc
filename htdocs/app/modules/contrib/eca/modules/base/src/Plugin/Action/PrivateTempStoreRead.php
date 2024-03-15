<?php

namespace Drupal\eca_base\Plugin\Action;

/**
 * Action to read value from the private temp store and store the result as a token.
 *
 * @Action(
 *   id = "eca_privatetempstore_read",
 *   label = @Translation("Private temporary store: read"),
 *   description = @Translation("Reads a value from the Drupal private temporary store by the given key. The result is stored in a token.")
 * )
 */
class PrivateTempStoreRead extends KeyValueStoreBase {

  /**
   * {@inheritdoc}
   */
  function store(string $collection) {
    return $this->privateTempStoreFactory->get($collection);
  }

}
