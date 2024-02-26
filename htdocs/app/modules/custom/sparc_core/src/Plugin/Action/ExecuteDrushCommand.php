<?php

namespace Drupal\sparc_core\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Symfony\Component\Process\Process;

/**
 * @Action(
 *   id = "sparc_core_execute_drush_command",
 *   label = @Translation("Execute drush-command")
 * )
 */
class ExecuteDrushCommand extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $command = ['drush'];
    /** @var \Drupal\eca\Plugin\DataType\DataTransferObject $data */
    $data = $this->tokenServices->getTokenData($this->configuration['command']);
    $command = array_merge($command, (array) $data->getValue()['values']);
    $process = new Process($command);
    $process->run();

    $this->tokenServices->addTokenData('command_is_successful', $process->isSuccessful());
    if (!$process->isSuccessful()) {
      $this->tokenServices->addTokenData($this->configuration['output_token_name'], $process->getErrorOutput());

      return;
    }

    $this->tokenServices->addTokenData($this->configuration['output_token_name'], $process->getOutput());
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = parent::defaultConfiguration();
    $config['command'] = '';
    $config['output_token_name'] = '';

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['command'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('The drush-command to run'),
      '#default_value' => $this->configuration['command'],
    ];

    $form['output_token_name'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('The token name which contains the output.'),
      '#default_value' => $this->configuration['command'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['command'] = $form_state->getValue('command');
    $this->configuration['output_token_name'] = $form_state->getValue('output_token_name');
  }

}
