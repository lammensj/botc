<?php

namespace Drupal\discord_php_eca\Plugin\Action;

use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Message;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\discord_php\Services\DiscordPhpManager\DiscordPhpManagerInterface;
use Drupal\eca\EcaState;
use Drupal\eca\Plugin\Action\ActionBase;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Action to perform message sending.
 *
 * @Action(
 *   id = "discord_php_eca_send_message",
 *   label = @Translation("Send message"),
 *   category = @Translation("DiscordPHP")
 * )
 */
class SendMessageAction extends ConfigurableActionBase {

  /**
   * Constructs a SendMessage-action.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\eca\Token\TokenInterface $token_services
   *   The token service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The current time.
   * @param \Drupal\eca\EcaState $state
   *   The ECA-state.
   * @param \Drupal\discord_php\Services\DiscordPhpManager\DiscordPhpManagerInterface $discordPhpManager
   *   The DiscordPHP manager.
   */
  final public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    TokenInterface $token_services,
    AccountProxyInterface $current_user,
    TimeInterface $time,
    EcaState $state,
    protected DiscordPhpManagerInterface $discordPhpManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $token_services, $current_user, $time, $state);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ActionBase {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('eca.token_services'),
      $container->get('current_user'),
      $container->get('datetime.time'),
      $container->get('eca.state'),
      $container->get('discord_php.services.discord_php_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $discord = $this->discordPhpManager->getClient();

    if (
      !empty($this->configuration['reply_to_id'])
      && !empty($this->configuration['reply_to_channel_id'])
    ) {
      $replyTo = new Message($discord, [
        'id' => $this->tokenServices->replace($this->configuration['reply_to_id']),
        'channel_id' => $this->tokenServices->replace($this->configuration['reply_to_channel_id']),
      ]);
    }

    $message = (MessageBuilder::new())
      ->setContent($this->tokenServices->replace($this->configuration['content']));
    if (isset($replyTo)) {
      $message->setReplyTo($replyTo);
    }

    if (!empty($this->configuration['filepath'])) {
      $filepath = $this->tokenServices->replace($this->configuration['filepath']);
      $message->addFile($filepath);
    }

    $discord->getChannel($this->tokenServices->replace($this->configuration['channel_id']))
      ->sendMessage($message);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'content' => '',
      'channel_id' => '',
      'reply_to_id' => '',
      'reply_to_channel_id' => '',
      'filepath' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['content'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Content'),
      '#default_value' => $this->configuration['content'],
      '#required' => TRUE,
    ];

    $form['channel_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Channel ID'),
      '#default_value' => $this->configuration['channel_id'],
      '#required' => TRUE,
    ];

    $form['reply_to_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reply-To ID'),
      '#default_value' => $this->configuration['reply_to_id'],
    ];

    $form['reply_to_channel_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reply-To Channel ID'),
      '#default_value' => $this->configuration['reply_to_channel_id'],
    ];

    $form['filepath'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Filepath'),
      '#default_value' => $this->configuration['filepath'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    if (empty($form_state->getValue('content'))) {
      $form_state->setErrorByName('content', $this->t('Content of the message can not be empty.'));
    }

    if (empty($form_state->getValue('channel_id'))) {
      $form_state->setErrorByName('content', $this->t('Channel ID of the message is not correct.'));
    }

    if (
      empty($form_state->getValue('reply_to_id'))
      && !empty($form_state->getValue('reply_to_channel_id'))
    ) {
      $form_state->setErrorByName('reply_to_id', $this->t('A reply-to channel ID without a reply-to ID is not allowed.'));
    }

    if (
      !empty($form_state->getValue('reply_to_id'))
      && empty($form_state->getValue('reply_to_channel_id'))
    ) {
      $form_state->setErrorByName('reply_to_channel_id', $this->t('A reply-to ID without a reply-to channel ID is not allowed.'));
    }

    if (!empty($form_state->getValue('filepath'))) {
      $filepath = $this->tokenServices->replace($form_state->getValue('filepath'));
      if (!file_exists($filepath)) {
        $form_state->setErrorByName('filepath', $this->t('Could not verify the specified filepath.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['content'] = $form_state->getValue('content');
    $this->configuration['channel_id'] = $form_state->getValue('channel_id');
    $this->configuration['reply_to_id'] = $form_state->getValue('reply_to_id');
    $this->configuration['reply_to_channel_id'] = $form_state->getValue('reply_to_channel_id');
    $this->configuration['filepath'] = $form_state->getValue('filepath');

    parent::submitConfigurationForm($form, $form_state);
  }

}
