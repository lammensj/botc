<?php

namespace Drupal\atak\Plugin\Action;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @Action(
 *    id = "atak_send_message",
 *    label = @Translation("Send message"),
 *    category = @Translation("Atak")
 *  )
 */
class SendChatMessage extends SendPresence {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->tokenServices->getTokenData($this->configuration['entity']);
    if ($node->get('field_prsnc_lur')->isEmpty()) {
      return;
    }

    try {
      $this->client->request(
        'POST',
        'http://35.206.145.140:19023/ManageChat/postChatToAll',
        [
          RequestOptions::HEADERS => [
            'Authorization' => 'Bearer token',
          ],
          RequestOptions::JSON => [
            'message' => $node->get('field_prsnc_lur')->getString(),
            'sender' => 'Admin',
          ],
        ],
      );
    }
    catch (ClientException $e) {
      VarDumper::dump($e);
    }
  }

}
