<?php

namespace Drupal\http_client_manager\Drush\Generators;

use DrupalCodeGenerator\Asset\AssetCollection as Assets;
use DrupalCodeGenerator\Attribute\Generator;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\GeneratorType;

/**
 * Code generator for a HTTP Client Manager service component.
 */
#[Generator(
  name: 'http_client_manager:service',
  description: 'Generates an HTTP Client Manager service.',
  aliases: ['http-service'],
  templatePath: __DIR__ . '/templates/service',
  type: GeneratorType::MODULE_COMPONENT,
)]
class ServiceGenerator extends BaseGenerator {

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars, Assets $assets): void {
    $ir = $this->createInterviewer($vars);
    $vars['machine_name'] = $ir->askMachineName();
    $vars['name'] = $ir->ask('Name');
    $vars['description'] = $ir->ask('Description');
    $vars['method'] = $ir->ask('Method (get or post)', 'get');
    $vars['base_uri'] = $ir->ask('Base URI', 'https://example.com');
    $vars['operation'] = $ir->ask('Name of operation');
    $vars['path'] = $ir->ask('Path (e.g. api/name/of/path)');
    $vars['summary'] = $ir->ask('Summary');

    $vars['id'] = mb_strtolower(str_replace([':', ' ', '-', '.', ',', '__'], '_', $vars['name']));

    $assets->addFile('{machine_name}.http_services_api.yml', 'http_services_api.yml.twig');
    $assets->addFile('src/api/{id}.yml', 'api.yml.twig');
    $assets->addFile('src/api/resources/{id}.yml', 'resources.yml.twig');
  }

}
