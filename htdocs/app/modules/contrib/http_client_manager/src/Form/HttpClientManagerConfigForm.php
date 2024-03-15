<?php

namespace Drupal\http_client_manager\Form;

use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HttpClientManagerConfigForm.
 *
 * @package Drupal\http_client_manager\Form
 */
class HttpClientManagerConfigForm extends ConfigFormBase {

  /**
   * The HTTP Service API Handler service.
   *
   * @var \Drupal\http_client_manager\HttpServiceApiHandlerInterface
   */
  protected $httpServicesApi;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->httpServicesApi = $container->get('http_client_manager.http_services_api');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'http_client_manager.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'http_client_manager_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('http_client_manager.settings');
    $form['enable_overriding_service_definitions'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable overriding of HTTP Services API definitions'),
      '#description' => $this->t('Check this option to enable overriding of HTTP Services API definitions.'),
      '#default_value' => $config->get('enable_overriding_service_definitions'),
    ];

    $overrides = $config->get('overrides');
    $form['overrides'] = [
      '#type' => 'table',
      '#header' => [
        'id' => $this->t('ID'),
        'title' => $this->t('Title'),
        'api_path' => $this->t('API Path'),
        'config' => $this->t('Configurations'),
        'commands' => $this->t('Commands'),
      ],
    ];

    $rows = [];
    foreach ($this->httpServicesApi->getServicesApi() as $service) {
      $id = $service['id'];
      $row = [
        'id' => $service['id'],
        'title' => [
          'data' => [
            '#type' => 'textfield',
            '#title' => $this->t('Override'),
            '#name' => 'overrides[' . $id . '][title]',
            '#value' => isset($overrides[$id]['title']) ? $overrides[$id]['title'] : NULL,
            '#description' => $this->t('Default value: @value', [
              '@value' => (isset($service['_original']['title']) ? $service['_original']['title'] : $service['title']),
            ]),
            '#description_display' => 'after',
          ],
        ],
        'api_path' => [
          'data' => [
            '#type' => 'textfield',
            '#title' => $this->t('Override'),
            '#name' => 'overrides[' . $id . '][api_path]',
            '#value' => isset($overrides[$id]['api_path']) ? $overrides[$id]['api_path'] : NULL,
            '#description' => $this->t('Default value: @value', [
              '@value' => (isset($service['_original']['api_path']) ? $service['_original']['api_path'] : $service['api_path']),
            ]),
            '#description_display' => 'after',
          ],
        ],
        'config' => [
          'data' => [
            'override' => [
              '#title' => $this->t('Override'),
              '#type' => 'textarea',
              '#name' => 'overrides[' . $id . '][config]',
              '#value' => isset($overrides[$id]['config']) ? Yaml::encode($overrides[$id]['config']) : NULL,
              '#rows' => 3,
              '#placeholder' => $this->t('Enter data in YAML format.'),
            ],
            'default' => [
              '#type' => 'details',
              '#title' => $this->t('Default value'),
              'value' => [
                '#markup' => '<pre>' . (!empty($service['_original']['config']) ? Yaml::encode($service['_original']['config']) : Yaml::encode($service['config'])) . '</pre>',
              ],
            ],
          ],
        ],
        'commands' => [
          'data' => [
            'override' => [
              '#title' => $this->t('Override'),
              '#type' => 'textarea',
              '#name' => 'overrides[' . $id . '][commands]',
              '#value' => isset($overrides[$id]['commands']) ? Yaml::encode($overrides[$id]['commands']) : NULL,
              '#rows' => 3,
              '#placeholder' => $this->t('Enter data in YAML format.'),
              '#element_validate' => [[$this, 'validateYaml']],
            ],
            'default' => [
              '#type' => 'details',
              '#title' => $this->t('Default value'),
              'value' => [
                '#markup' => '<pre>' . (!empty($service['_original']['commands']) ? Yaml::encode($service['_original']['commands']) : NULL) . '</pre>',
              ],
            ],
          ],
        ],
      ];
      $rows[] = $row;
    }

    $form['overrides']['#rows'] = $rows;

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $overrides = $form_state->getValue('overrides');
    foreach ($overrides as $id => $override) {
      foreach (['config', 'commands'] as $setting) {
        if (!empty($setting)) {
          try {
            $overrides[$id][$setting] = Yaml::decode($overrides[$id][$setting]);
          }
          catch (InvalidDataTypeException $e) {
            $message = $this->t('There was an error while parsing your YAML data: @message', [
              '@message' => $e->getMessage(),
            ]);
            $this->messenger()->addError($message);
            continue;
          }
        }
      }
      $overrides[$id] = array_filter($overrides[$id]);
    }
    $overrides = array_filter($overrides);

    $this->config('http_client_manager.settings')
      ->set('enable_overriding_service_definitions', $form_state->getValue('enable_overriding_service_definitions'))
      ->set('overrides', $overrides)
      ->save();
  }

}
