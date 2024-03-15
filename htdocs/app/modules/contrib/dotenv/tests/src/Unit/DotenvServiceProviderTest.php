<?php

namespace Drupal\Tests\dotenv\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\dotenv\DotenvServiceProvider;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the dotenv service provider.
 *
 * @coversDefaultClass \Drupal\dotenv\DotenvServiceProvider
 *
 * @group Dotenv
 */
class DotenvServiceProviderTest extends UnitTestCase {

  /**
   * Tests the dotenv service provider.
   *
   * @dataProvider dataProvider
   */
  public function testRegister(?string $appEnv, string $expectedEnv): void {
    $container = new ContainerBuilder();
    $dotenvServiceProvider = new DotenvServiceProvider();

    if (isset($_ENV['APP_ENV'])) {
        $originalEnv = $_ENV['APP_ENV'];
    }

    $_ENV['APP_ENV'] = $appEnv;
    $dotenvServiceProvider->register($container);

    if (isset($originalEnv)) {
        $_ENV['APP_ENV'] = $originalEnv;
    }

    static::assertEquals(DRUPAL_ROOT . '/..', $container->getParameter('dotenv.project_dir'));
    static::assertEquals($expectedEnv, $container->getParameter('dotenv.environment'));
  }

  /**
   * Provides some test cases.
   */
  public function dataProvider(): array {
    return [
      'When no APP_ENV is set, it fallback to "prod"' => [
        NULL,
        'prod',
      ],
      'It uses the APP_ENV to populate "dotenv.environment"' => [
        'foobar',
        'foobar',
      ],
    ];
  }

}
