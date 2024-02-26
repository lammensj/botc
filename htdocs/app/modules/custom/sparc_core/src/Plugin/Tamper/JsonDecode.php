<?php

namespace Drupal\sparc_core\Plugin\Tamper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tamper\TamperableItemInterface;
use Drupal\tamper\TamperBase;
use GuzzleHttp\Utils;

/**
 * Plugin implementation for json_decoding.
 *
 * @Tamper(
 *   id = "json_decode",
 *   label = @Translation("citadel_tamper.labels.json_decode"),
 *   description = @Translation("citadel_tamper.descriptions.json_decode"),
 *   category = "Text",
 *   handle_multiples = FALSE
 * )
 */
class JsonDecode extends TamperBase {

  const SETTING_ASSOC = 'assoc';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = parent::defaultConfiguration();
    $config[self::SETTING_ASSOC] = FALSE;

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form[self::SETTING_ASSOC] = [
      '#type' => 'checkbox',
      '#title' => $this->t('citadel_tamper.titles.assoc'),
      '#default_value' => $this->getSetting(self::SETTING_ASSOC),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);

    $this->setConfiguration([
      self::SETTING_ASSOC => (bool) $form_state->getValue(self::SETTING_ASSOC),
    ]);
  }

  /**
   * @inheritDoc
   */
  public function tamper($data, TamperableItemInterface $item = NULL) {
    $assoc = (bool) $this->getSetting(self::SETTING_ASSOC);

    return Utils::jsonDecode($data, $assoc);
  }

}
