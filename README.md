# BOTC

## TLDR;
Battlefield of Things Challenge

## Setup
- Download and install [Docksal](https://docksal.io/installation)
- If you want a local version of Grafana and InfluxDB:
  - `cp ./.docksal/etc/grafana/config/.tpl.env ./.docksal/etc/grafana/config/.env;`
  - Adjust `./.docksal/etc/grafana/config/.env`
  - `cp ./.docksal/etc/influxdb/config/.tpl.env ./.docksal/etc/influxdb/config/.env;`
  - Adjust `./.docksal/etc/influxdb/config/.env`
- `fin config`: verify/dry-run your local installation
- `fin p up`: pull the images and start containers
- `fin composer install`
- `cp ./htdocs/.env.example ./htdocs/.env;`
- `cp ./assets/settings.local.php ./htdocs/app/sites/default/settings.local.php;`
- `fin drush si --existing-config`: install the site using existing config
- `fin drush uli`: log in
