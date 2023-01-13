<?php

namespace Drupal\influxdb\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Http\Adapter\Guzzle6\Client as HttpClient;
use Http\Client\Exception\NetworkException;
use InfluxDB2\Client;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Configure InfluxDB settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'influxdb_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['influxdb.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['server_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('influxdb.labels.server_url', [], ['context' => 'influxdb']),
      '#required' => TRUE,
      '#default_value' => $this->config('influxdb.settings')->get('server_url'),
      '#placeholder' => 'http://localhost:8086'
    ];
    $form['token'] = [
      '#type' => 'key_select',
      '#title' => $this->t('influxdb.labels.token', [], ['context' => 'influxdb']),
      '#required' => TRUE,
      '#default_value' => $this->config('influxdb.settings')->get('token'),
      '#key_filters' => [
        'type' => 'authentication',
      ],
    ];
    $form['allow_redirects'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('influxdb.labels.allow_redirects', [], ['context' => 'influxdb']),
      '#default_value' => $this->config('influxdb.settings')->get('allow_redirects'),
    ];
    $form['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('influxdb.labels.debug', [], ['context' => 'influxdb']),
      '#default_value' => $this->config('influxdb.settings')->get('debug'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $http = new HttpClient();
    $client = new Client([
      'url' => $form_state->getValue('server_url'),
      'httpClient' => $http,
    ]);

    try {
      $client->ping();
    } catch (\Exception $e) {
      $form_state->setErrorByName('server_url', $e->getMessage());
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('influxdb.settings')
      ->set('server_url', $form_state->getValue('server_url'))
      ->set('token', $form_state->getValue('token'))
      ->set('allow_redirects', $form_state->getValue('allow_redirects'))
      ->set('debug', $form_state->getValue('debug'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
