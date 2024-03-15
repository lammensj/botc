Dotenv
======================

> Integrates the Symfony Dotenv component with Drupal

## What's the Symfony Dotenv Component

> Symfony Dotenv parses .env files to make environment variables stored in them accessible via $_SERVER or $_ENV.

[https://symfony.com/components/Dotenv](https://symfony.com/components/Dotenv)

## Why?

A `dotenv` file allows you to remove hardcoded credentials or config from your code. For an extensive explanation on why this is a good thing, check out [the _Config_ chapter of the Twelve-Factor App website](https://www.12factor.net/config).

## Configuration

Add a `.env` file in the root of your project. It has to at least contain the `APP_ENV` environment variable:

```dotenv
APP_ENV=prod
```

In your `settings.php` file, add the following to the top of the file:

```php
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(DRUPAL_ROOT . '/../.env');
```

## How does it work?

You can now add environment variables to your `.env` file and it will automatically be available in the `$_ENV` global var.

You can use it in `settings.php`, in service providers or in other places throughout your code. Some examples:

```php
<?php
// settings.php

$databases['default']['default'] = [
    'database' => $_ENV['DB_DATABASE'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'prefix' => '',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'driver' => 'mysql',
];

$config['mandrill.settings'] = [
    'mandrill_api_key' => $_ENV['MANDRILL_API_KEY'],
];
```
```php
<?php

namespace Drupal\yourmodule;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

class YourmoduleServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $container)
    {
        $container->setParameter('yourmodule.some_secret',  $_ENV['SOME_SECRET']);
    }
}
```

On live environments, you should invoke <code>drush dotenv:dump</code> every time your .env file changes. If you don't, the .env file will be loaded at every request, which will decrease the performance of your application.

You can use the `drush dotenv:dump` command to get debugging info about the scanned dotenv files and the loaded variables.

Read the [Symfony documentation](https://symfony.com/doc/current/configuration.html#configuring-environment-variables-in-env-files) for more information.
