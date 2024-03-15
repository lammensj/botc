<?php

namespace Drupal\Tests\plugin\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;

/**
 * \Drupal\plugin\Plugin\Field\FieldWidget\PluginSelector integration test.
 *
 * @group Plugin
 */
class PluginSelectorTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'field_ui', 'plugin'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the widget.
   */
  public function testWidget() {
    $this->rebuildAll();
    $user = $this->drupalCreateUser(['administer user fields']);
    $this->drupalLogin($user);

    // Test the widget when setting a default field value.
    $field_name = strtolower($this->randomMachineName());
    $selectable_plugin_type_id = 'block';
    $field_type = 'plugin:' . $selectable_plugin_type_id;
    $default_selected_plugin_id = 'user_login_block';
    $this->drupalGet('admin/config/people/accounts/fields/add-field');
    $this->submitForm([
      'label' => $this->randomString(),
      'field_name' => $field_name,
      'new_storage_type' => $field_type,
    ], t('Save and continue'));
    $this->submitForm([], t('Save field settings'));
    $this->submitForm([
      sprintf('default_value_input[field_%s][0][plugin_selector][container][select][container][plugin_id]', $field_name) => $default_selected_plugin_id,
    ], t('Choose'));
    $this->submitForm([], t('Save settings'));
    \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();
    // Get all plugin fields.
    $field_storage_id = 'user.field_' . $field_name;
    $field_storage = FieldStorageConfig::load($field_storage_id);
    $this->assertNotNull($field_storage);
    $field_id = 'user.user.field_' . $field_name;
    /** @var \Drupal\field\FieldConfigInterface $field */
    $field = FieldConfig::load($field_id);
    $this->assertNotNull($field);
    $this->assertEquals($field->getDefaultValueLiteral()[0]['plugin_id'], $default_selected_plugin_id);
    $this->assertTrue(is_array($field->getDefaultValueLiteral()[0]['plugin_configuration']));

    // Test the widget when creating an entity.
    $entity_selected_plugin_id = 'system_breadcrumb_block';
    $this->drupalGet('user/' . $user->id() . '/edit');
    $this->submitForm([
      sprintf('field_%s[0][plugin_selector][container][select][container][plugin_id]', $field_name) => $entity_selected_plugin_id,
    ], t('Choose'));
    $this->submitForm([], t('Save'));

    // Test whether the widget displays field values.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $user */
    $storage = \Drupal::entityTypeManager()->getStorage('user');
    $storage->resetCache();
    $user = $storage->load($user->id());
    $this->assertEquals($entity_selected_plugin_id, $user->get('field_' . $field_name)->get(0)->get('plugin_id')->getValue());
  }
}
