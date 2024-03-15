<?php

namespace Drupal\Tests\solcast_eca\Kernel\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Action\ActionManager;
use Drupal\eca\Token\TokenServices;
use Drupal\KernelTests\KernelTestBase;
use Drupal\solcast_eca\IntervalOperation;

/**
 * Tests that the ECA-action can calculate the start of the interval.
 *
 * @group solcast_eca
 */
class SetIntervalStartTest extends KernelTestBase {

  /**
   * The action manager.
   *
   * @var \Drupal\Core\Action\ActionManager|null
   */
  protected ?ActionManager $actionManager;

  /**
   * The token service.
   *
   * @var \Drupal\eca\Token\TokenServices|null
   */
  protected ?TokenServices $tokenService;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'eca',
    'solcast_eca',
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(self::$modules);

    $this->actionManager = $this->container->get('plugin.manager.action');
    $this->tokenService = $this->container->get('eca.token_services');
  }

  /**
   * Tests that setting the interval is executed correctly.
   *
   * @dataProvider intervalDataProvider
   */
  public function testIntervalStart(array $actionConfig, string $replacedValue, AccessResultInterface $accessResult): void {
    /** @var \Drupal\solcast_eca\Plugin\Action\SetIntervalStart $action */
    $action = $this->actionManager->createInstance('solcast_eca_set_interval_start', $actionConfig);
    $action->execute();

    $this->assertEquals($replacedValue, $this->tokenService->replaceClear(sprintf('[%s]', $actionConfig['eca_token_name'])));

    $this->tokenService->addTokenData('datetime_format', $actionConfig['datetime_format']);
    $this->tokenService->addTokenData('datetime', $actionConfig['datetime']);
    $this->tokenService->addTokenData('period', $actionConfig['period']);
    $this->assertEquals($accessResult, $action->access([], NULL, TRUE));
  }

  /**
   * Provides different combinations of valid datetimes, periods and operations.
   *
   * @return \Generator
   *   A collection of action-config and the expected result.
   */
  public function intervalDataProvider(): \Generator {
    yield [
      [
        'datetime' => '2023-10-22T17:30:00',
        'datetime_format' => 'Y-m-d\TH:i:s',
        'period' => 'PT30M',
        'operation' => IntervalOperation::SUBTRACT->value,
        'eca_token_name' => 'solcast_eca_token_result',
      ],
      '2023-10-22T17:00:00',
      AccessResult::allowed(),
    ];

    yield [
      [
        'datetime' => '2023-10-22T17:30:00',
        'datetime_format' => 'Y-m-d\TH:i:s',
        'period' => 'PT15M',
        'operation' => IntervalOperation::SUBTRACT->value,
        'eca_token_name' => 'solcast_eca_token_result',
      ],
      '2023-10-22T17:15:00',
      AccessResult::allowed(),
    ];

    yield [
      [
        'datetime' => '2023-10-22T17:30:00',
        'datetime_format' => 'Y-m-d\TH:i:s',
        'period' => 'PT30M',
        'operation' => IntervalOperation::ADD->value,
        'eca_token_name' => 'solcast_eca_token_result',
      ],
      '2023-10-22T18:00:00',
      AccessResult::allowed(),
    ];

    $period = $this->getRandomGenerator()->string();
    yield [
      [
        'datetime' => '2023-10-22T17:30:00',
        'datetime_format' => 'Y-m-d\TH:i:s',
        'period' => $period,
        'operation' => IntervalOperation::ADD->value,
        'eca_token_name' => 'solcast_eca_token_result',
      ],
      '',
      AccessResult::forbidden(sprintf('Could not interpret interval: Unknown or bad format (%s).', $period)),
    ];

    $datetime = $this->getRandomGenerator()->string();
    yield [
      [
        'datetime' => $datetime,
        'datetime_format' => 'Y-m-d\TH:i:s',
        'period' => 'PT30M',
        'operation' => IntervalOperation::ADD->value,
        'eca_token_name' => 'solcast_eca_token_result',
      ],
      '',
      AccessResult::forbidden(sprintf('The provided datetime \'%s\' and format \'Y-m-d\TH:i:s\' are not valid.', $datetime)),
    ];

    $datetimeFormat = $this->getRandomGenerator()->string();
    yield [
      [
        'datetime' => '2023-10-22T17:30:00',
        'datetime_format' => $datetimeFormat,
        'period' => 'PT30M',
        'operation' => IntervalOperation::ADD->value,
        'eca_token_name' => 'solcast_eca_token_result',
      ],
      '',
      AccessResult::forbidden(sprintf('The provided datetime \'2023-10-22T17:30:00\' and format \'%s\' are not valid.', $datetimeFormat)),
    ];

    $datetime = $this->getRandomGenerator()->string();
    $datetimeFormat = $this->getRandomGenerator()->string();
    yield [
      [
        'datetime' => $datetime,
        'datetime_format' => $datetimeFormat,
        'period' => 'PT30M',
        'operation' => IntervalOperation::ADD->value,
        'eca_token_name' => 'solcast_eca_token_result',
      ],
      '',
      AccessResult::forbidden(sprintf('The provided datetime \'%s\' and format \'%s\' are not valid.', $datetime, $datetimeFormat)),
    ];
  }

}
