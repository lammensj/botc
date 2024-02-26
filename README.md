# SPARC by Drupal

## TLDR;
SPARC is Solar Powered, Advanced, Renewable Control for optimizing self-consumption of electricty.

## Introduction
SPARC is an idea on how to solve the problem that, as a home owner with a photovoltaic system, we're pushed to reduce energy usage from non-renewable sources like coal or nuclear. But how can you do that without investing in a ton of smart devices and an energy management system of a specific vendor? Are you comfortable that your appliances are connected to the outside world? I mean, those platforms and those integrations are still developed and managed by humans, and thus error-prone (1)(2).

What I wanted to achieve, is very simple: when I rise in the morning, I want to see a notification on my phone on how to schedule my dishwasher and/or my washing machine. I can then prepare them, delay their execution and go to work.
But also ask questions: "What's the best time period today to start the pyrolysis-process of my oven?", "Give me an overview of the estimated output for the next 5 days.".

By levering Drupal, it has become a project consisting of contrib and custom modules for time-based storage, processing via SOLID-principles and logging the output in a message-like way.

## Disclaimer
I am not responsible, nor can I be kept accountable for any change that might occur in your home situation as a result of what you've learned here. If you suddenly find yourself in charge of the entire household because you found this valuable and wanted to try it yourself, that's on you 😉

## Features MVP
- ✅ Provide http-integration for the [Solcast](https://solcast.com/)-API
- ❓ Provide http-integration for the [Forecast.Solar](https://forecast.solar/)-API
- ✅ Run an InfluxDB-service
- ✅ Provide php-integration for the InfluxDB-service
- ✅ Provide php-integration for Discord
- ✅ Create ECA-models for fetching data and providing calculations

## Methodology
### Contrib Modules
- [BPMN.iO](https://www.drupal.org/project/bpmn_io): BPMN.iO is a BPMN modeller for [ECA](https://www.drupal.org/project/eca) and is fully integrated into Drupal's admin UI.
- [ECA: Event - Condition - Action](https://www.drupal.org/project/eca): ECA is a powerful, versatile, and user-friendly rules engine for Drupal 9+. The core module is a processor that validates and executes event-condition-action plugins;
- [ECA Tamper Integration](https://www.drupal.org/project/eca_tamper): Integrates [ECA](https://www.drupal.org/project/eca) with [Tamper](https://www.drupal.org/project/tamper);
- [HTTP Client Manager](https://www.drupal.org/project/http_client_manager): Http Client Manager introduces a new Guzzle based plugin which allows you to manage HTTP clients using Guzzle Service Descriptions via YAML, JSON or PHP files, in a simple and efficient way;
### Contributed Modules
- [DiscordPHP](https://www.drupal.org/project/discord_php): using the team-reflex/discord-php-package, this module offers a drush-command to start the included ReactPHP-loop and dispatches Symfony-events for certain things that happen inside it;
- [Solcast](https://www.drupal.org/project/solcast): this module exposes HTTP Client Manager-resources for the Solcast API. This platform provides solar resource assessment and forecasting data for irradiance and PV power;
### Custom Modules
### ECA-Models

## Results

## Conclusion

## References
(1) https://hackaday.com/2022/03/18/welcome-to-the-future-where-your-microwave-thinks-its-a-steam-oven/
(2) https://thenextweb.com/news/update-brainwashes-microwaves-thinking-theyre-steam-ovens
