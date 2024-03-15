<?php

namespace Drupal\dotenv\Commands;

use Consolidation\AnnotatedCommand\AnnotationData;
use Drupal\Core\Database\Database;
use DrupalFinder\DrupalFinder;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Initialize .env integration for this project.
 */
class DotenvInitCommand extends DrushCommands {

  /**
   * The Drupal site path.
   *
   * @var string
   */
  protected string $sitePath;

  /**
   * The Drupal & Composer root paths finder.
   *
   * @var \DrupalFinder\DrupalFinder
   */
  protected DrupalFinder $drupalFinder;

  /**
   * The filesystem.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected Filesystem $filesystem;

  /**
   * Create a new DotenvInitCommand instance.
   *
   * @param string $sitePath
   *   The Drupal site path.
   */
  public function __construct(string $sitePath) {
    parent::__construct();
    $this->drupalFinder = new DrupalFinder();
    $this->drupalFinder->locateRoot(__DIR__);
    $this->filesystem = new Filesystem();
    $this->sitePath = $this->drupalFinder->getDrupalRoot() . DIRECTORY_SEPARATOR . $sitePath;
  }

  /**
   * Initialize the .env integration for this project.
   *
   * @param array $options
   *   The command options.
   *
   * @option database-settings
   *   Whether to add the database settings to the .env file.
   * @option add-loader
   *   Whether to create the loader file and autoload it using Composer.
   * @option add-gitignore
   *   Whether to add the .env file to the .gitignore file.
   *
   * @command dotenv:init
   */
  public function init(array $options = [
    'database-settings' => TRUE,
    'add-loader' => TRUE,
    'add-gitignore' => TRUE,
  ]): void {
    $dotEnvPath = $this->drupalFinder->getComposerRoot() . '/.env';
    if ($this->filesystem->exists($dotEnvPath)) {
      $this->logger()->warning('The .env file already exists at path {path}', ['path' => $dotEnvPath]);
      return;
    }

    $dotEnvExamplePath = $this->drupalFinder->getComposerRoot() . '/.env.example';
    if ($this->filesystem->exists($dotEnvExamplePath)) {
      $this->logger()->warning('The .env.example file already exists at path {path}', ['path' => $dotEnvExamplePath]);
      return;
    }

    $settingsPhpPath = $this->sitePath . '/settings.php';
    if (!$this->filesystem->exists($settingsPhpPath)) {
      $this->logger()->warning('The settings.php file does not exist at path {path}', ['path' => $settingsPhpPath]);
      return;
    }

    $dotEnvFile = fopen($dotEnvPath, 'w');
    $dotEnvExampleFile = fopen($dotEnvExamplePath, 'w');
    $settingsPhpFile = fopen($settingsPhpPath, 'a');

    fwrite($dotEnvExampleFile, 'APP_ENV=' . PHP_EOL);
    fwrite($dotEnvFile, 'APP_ENV=' . (getenv('APP_ENV') ?: 'prod') . PHP_EOL);

    if ($options['database-settings']) {
      $info = Database::getConnectionInfo();
      $keyMap = [
        'database' => 'DB_NAME',
        'username' => 'DB_USER',
        'password' => 'DB_PASSWORD',
        'host' => 'DB_HOST',
        'port' => 'DB_PORT',
        'driver' => 'DB_DRIVER',
        'prefix' => 'DB_PREFIX',
        'collation' => 'DB_COLLATION',
      ];

      fwrite($dotEnvExampleFile, PHP_EOL);
      fwrite($dotEnvFile, PHP_EOL);
      fwrite($settingsPhpFile, PHP_EOL);

      foreach ($keyMap as $settingsKey => $dotEnvKey) {
        if (!isset($info['default'][$settingsKey])) {
          continue;
        }

        fwrite($dotEnvExampleFile, sprintf('%s=', $dotEnvKey) . PHP_EOL);
        fwrite($dotEnvFile, sprintf('%s=%s', $dotEnvKey, $info['default'][$settingsKey]) . PHP_EOL);
        fwrite($settingsPhpFile, sprintf("\$databases['default']['default']['%s'] = \$_ENV['%s'];", $settingsKey, $dotEnvKey) . PHP_EOL);
      }
    }

    fclose($dotEnvExampleFile);
    fclose($dotEnvFile);
    fclose($settingsPhpFile);

    $this->logger()->success('The .env and .env.example files have been created.');

    if ($options['add-loader']) {
      $this->filesystem->copy(__DIR__ . '/../../files/load.environment.php', $this->drupalFinder->getComposerRoot() . '/load.environment.php');

      $composerJsonPath = $this->drupalFinder->getComposerRoot() . '/composer.json';
      if (!$this->filesystem->exists($composerJsonPath)) {
        $this->logger()->warning('The composer.json file does not exist at path {path}', ['path' => $composerJsonPath]);
        return;
      }

      $composerJson = json_decode(file_get_contents($composerJsonPath), TRUE);
      $composerJson['autoload']['files'][] = 'load.environment.php';
      file_put_contents($composerJsonPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

      $process = $this->processManager()->process(['composer', 'dump-autoload']);
      $process->setWorkingDirectory($this->drupalFinder->getComposerRoot());
      $process->disableOutput();
      $process->mustRun();

      $this->logger()->success('The load.environment.php file has been created and added to the autoload.files in composer.json.');
    }

    if ($options['add-gitignore']) {
      $gitIgnorePath = $this->drupalFinder->getComposerRoot() . '/.gitignore';
      if (!$this->filesystem->exists($gitIgnorePath)) {
        $this->logger()->warning('The .gitignore file does not exist at path {path}', ['path' => $gitIgnorePath]);
        return;
      }

      $gitIgnoreFile = fopen($gitIgnorePath, 'a');
      fwrite($gitIgnoreFile, PHP_EOL);
      fwrite($gitIgnoreFile, '# Ignore .env files as they are personal' . PHP_EOL);
      fwrite($gitIgnoreFile, '.env' . PHP_EOL);
      fclose($gitIgnoreFile);

      $this->logger()->success('The .env file has been added to the .gitignore file.');
    }
  }

  /**
   * Fill the command options by prompting the user.
   *
   * @hook interact dotenv:init
   */
  public function interact(InputInterface $input, OutputInterface $output, AnnotationData $annotationData): void {
    $this->input->setOption(
          'database-settings',
          $this->input->getOption('database-settings') ?? $this->askDatabaseSettings()
      );
    $this->input->setOption(
          'add-loader',
          $this->input->getOption('add-loader') ?? $this->askAddLoader()
      );
    $this->input->setOption(
          'add-gitignore',
          $this->input->getOption('add-gitignore') ?? $this->askAddGitignore()
      );
  }

  /**
   * Ask the user if they want to add the database settings to the .env file.
   */
  protected function askDatabaseSettings(): bool {
    return $this->io()->confirm(dt('Do you want to add database settings to the .env file?'));
  }

  /**
   * Ask the user if they want to add the load.environment.php file.
   */
  protected function askAddLoader(): bool {
    return $this->io()->confirm(dt('Do you want to add the .env loader file to autoload with Composer?'));
  }

  /**
   * Ask the user if they want to add the .env file to the .gitignore file.
   */
  protected function askAddGitignore(): bool {
    return $this->io()->confirm(dt('Do you want to add the .env file to the .gitignore file?'));
  }

}
