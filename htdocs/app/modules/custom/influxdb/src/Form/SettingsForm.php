<?php

namespace Drupal\influxdb\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\State;
use Drupal\influxdb\Services\ClientFactory\ClientFactory;
use Http\Adapter\Guzzle7\Client as HttpClient;
use InfluxDB2\ApiException;
use InfluxDB2\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure InfluxDB settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The client factory.
   *
   * @var \Drupal\influxdb\Services\ClientFactory\ClientFactory
   */
  protected ClientFactory $clientFactory;

  /**
   * The state.
   *
   * @var \Drupal\Core\State\State
   */
  protected State $state;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): FormInterface {
    $instance = parent::create($container);
    $instance->clientFactory = $container->get('influxdb.services.client_factory');

    return $instance;
  }

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
    $form['organization'] = [
      '#type' => 'textfield',
      '#title' => $this->t('influxdb.labels.organization', [], ['context' => 'influxdb']),
      '#required' => TRUE,
      '#default_value' => $this->config('influxdb.settings')->get('organization'),
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
      'token' => '',
    ]);

    try {
      $response = $client->ping();
      $this->messenger()->addStatus(sprintf('X-Influxdb-Version: %s', $response['X-Influxdb-Version'][0]));
    } catch (\Exception $e) {
      $form_state->setErrorByName('server_url', $e->getMessage());
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('influxdb.settings')
      ->set('server_url', $form_state->getValue('server_url'))
      ->set('organization', $form_state->getValue('organization'))
      ->set('token', $form_state->getValue('token'))
      ->set('allow_redirects', $form_state->getValue('allow_redirects'))
      ->set('debug', $form_state->getValue('debug'))
      ->save();

    try {
      $response = $this->clientFactory->getOrganizationId($form_state->getValue('organization'));
      $this->messenger()->addStatus($this->t('influxdb.messages.organization_id', [
        '@id' => $response,
      ]));
    }
    catch (ApiException $e) {
      $this->messenger()->addError($e->getMessage());
    }

    parent::submitForm($form, $form_state);
  }

}
