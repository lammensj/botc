<?php

namespace Drupal\solcast_eca\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\solcast_eca\IntervalOperation;

/**
 * Provides an ECA-action to calculate the start of the interval.
 *
 * @Action(
 *   id = "solcast_eca_set_interval_start",
 *   label = @Translation("Calculate interval"),
 *   description = @Translation("Calculates the other end of the interval, either by adding or subtracting the provided period."),
 * )
 */
class SetIntervalStart extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    try {
      $period = new \DateInterval($this->tokenServices->replaceClear($this->configuration['period']));
    }
    catch (\Exception $e) {
      return;
    }

    $format = $this->tokenServices->replaceClear($this->configuration['datetime_format']);
    $datetime = $this->tokenServices->replaceClear($this->configuration['datetime']);

    $dt = \DateTime::createFromFormat($format, $datetime);
    if ($dt === FALSE) {
      return;
    }

    $dt = call_user_func([$dt, $this->configuration['operation']], $period);
    $this->tokenServices->addTokenData($this->configuration['eca_token_name'], $dt->format($format));
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE): bool|AccessResultInterface {
    $result = AccessResult::allowed();

    // Validate datetime and format.
    $format = $this->tokenServices->replaceClear($this->configuration['datetime_format']);
    $datetime = $this->tokenServices->replaceClear($this->configuration['datetime']);
    $dt = \DateTime::createFromFormat($format, $datetime);
    if ($dt === FALSE) {
      $result = AccessResult::forbidden(sprintf('The provided datetime \'%s\' and format \'%s\' are not valid.', $datetime, $format));

      return $return_as_object ? $result : $result->isAllowed();
    }

    // Validate period.
    try {
      new \DateInterval($this->tokenServices->replaceClear($this->configuration['period']));
    }
    catch (\Exception $e) {
      $result = AccessResult::forbidden(sprintf('Could not interpret interval: %s.', $e->getMessage()));

      return $return_as_object ? $result : $result->isAllowed();
    }

    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'datetime' => '',
      'datetime_format' => '',
      'period' => '',
      'operation' => IntervalOperation::SUBTRACT->value,
      'eca_token_name' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['datetime'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Datetime'),
      '#description' => $this->t('A string representing the datetime of the end of the interval.'),
      '#placeholder' => date('d-m-Y\TH:i:s.u0p'),
      '#default_value' => $this->configuration['datetime'],
      '#required' => TRUE,
    ];

    $form['datetime_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Datetime format'),
      '#description' => $this->t('The format of the provided datetime-string. See the <a href="https://www.php.net/manual/datetime.format.php#refsect1-datetime.format-parameters">PHP manual</a> for available options.'),
      '#placeholder' => 'd-m-Y\TH:i:s.u0p',
      '#default_value' => $this->configuration['datetime_format'],
      '#required' => TRUE,
    ];

    $form['period'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Period'),
      '#description' => $this->t('The period denoting the length of the interval.'),
      '#placeholder' => 'PT30M',
      '#default_value' => $this->configuration['period'],
      '#required' => TRUE,
    ];

    $form['operation'] = [
      '#type' => 'select',
      '#options' => IntervalOperation::options(),
      '#title' => $this->t('Operation'),
      '#description' => $this->t('The operation to perform on the converted datetime, using the provided period.'),
      '#default_value' => $this->configuration['operation'],
      '#required' => TRUE,
    ];

    $form['eca_token_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ECA token name'),
      '#description' => $this->t('The name of the token which will contain the result of the operation.'),
      '#default_value' => $this->configuration['eca_token_name'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $format = $this->tokenServices->replaceClear($form_state->getValue('datetime_format'));
    $datetime = $this->tokenServices->replaceClear($form_state->getValue('datetime'));

    if (!empty($format) && !empty($datetime)) {
      $dt = \DateTime::createFromFormat($format, $datetime);

      if ($dt === FALSE) {
        $form_state->setErrorByName('datetime_format', $this->t("The provided datetime '@datetime' and format '@format' are not valid.", [
          '@datetime' => $form_state->getValue('datetime'),
          '@format' => $form_state->getValue('datetime_format'),
        ]));
        $form_state->setErrorByName('datetime', $this->t("The provided datetime '@datetime' and format '@format' are not valid.", [
          '@datetime' => $form_state->getValue('datetime'),
          '@format' => $form_state->getValue('datetime_format'),
        ]));
      }
    }

    $period = $this->tokenServices->replaceClear($this->configuration['period']);
    if (!empty($period)) {
      try {
        new \DateInterval($period);
      }
      catch (\Exception $e) {
        $form_state->setErrorByName('period', $this->t("Could not interpret interval: @message.", [
          '@message' => $e->getMessage(),
        ]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['datetime'] = $form_state->getValue('datetime');
    $this->configuration['datetime_format'] = $form_state->getValue('datetime_format');
    $this->configuration['period'] = $form_state->getValue('period');
    $this->configuration['operation'] = $form_state->getValue('operation');
    $this->configuration['eca_token_name'] = $form_state->getValue('eca_token_name');
  }

}
