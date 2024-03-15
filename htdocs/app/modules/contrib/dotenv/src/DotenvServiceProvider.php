<?php

namespace Drupal\dotenv;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * The dotenv service provider.
 *
 * Sets container parameters for configuring the Symfony Console commands.
 */
class DotenvServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $container->setParameter('dotenv.project_dir', DRUPAL_ROOT . '/..');
    $container->setParameter('dotenv.environment', $_ENV['APP_ENV'] ?? 'prod');
  }

}
