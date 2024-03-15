<?php
/**
 * @file
 * Default drupal settings file to work with docksal.
 */
// Set the paths to user files and tmp directory.
$config['system.file']['path']['temporary'] = '/tmp';

// Workaround for permission issues with NFS shares in Vagrant
$settings['file_chmod_directory'] = 0755;
$settings['file_chmod_file'] = 0666;

// Reverse proxy configuration (Docksal's vhost-proxy)
if (PHP_SAPI !== 'cli') {
  $settings['reverse_proxy'] = TRUE;
  $settings['reverse_proxy_addresses'] = [$_SERVER['REMOTE_ADDR']];
  // HTTPS behind reverse-proxy
  if (
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' &&
    !empty($settings['reverse_proxy']) && in_array($_SERVER['REMOTE_ADDR'], $settings['reverse_proxy_addresses'])
  ) {
    $_SERVER['HTTPS'] = 'on';
    // This is hardcoded because there is no header specifying the original port.
    $_SERVER['SERVER_PORT'] = 443;
  }
}

// Use the docksal VIRTUAL_HOST value.
$trusted_host = str_replace('.', '\.', getenv('VIRTUAL_HOST'));
$settings['trusted_host_patterns'] = [$trusted_host];
$settings['trusted_host_patterns'][] = '35.206.145.140';

/**
 * Enable local development services.
 */
$settings['skip_permissions_hardening'] = TRUE;

// An improved dev services.yml file
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';

$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';

$config['system.logging']['error_level'] = 'verbose';

// Disable caching.
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['css']['gzip'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$config['system.performance']['js']['gzip'] = FALSE;

$settings['hash_salt'] = 'development_salt';

// Enable/disable readonly mode.
$settings['config_sync_directory'] = '../../config/sync';
