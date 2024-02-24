<?php

namespace Drupal\influxdb\Commands;

use Drupal\Component\Uuid\Uuid;
use Drupal\eca_base\BaseEvents;
use Drupal\eca_base\Event\CustomEvent;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\VarDumper;

class RecommendCommand extends Command {

  protected array $elements = [];

  protected static $defaultName = 'influxdb:recommend';

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->testFromGeneralMemoryAllocation();

//    $event = new CustomEvent('influxdb');
//    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
//    $dispatcher = \Drupal::service('event_dispatcher');
//    $dispatcher->dispatch($event, BaseEvents::CUSTOM);

    return 0;
  }

  protected function testFromGeneralMemoryAllocation() {
    // Best Fit.
    $blocks = [
      new MemoryBlock('1', 25),
      new MemoryBlock('2', 50),
      new MemoryBlock('3', 100),
      new MemoryBlock('4', 200),
      new MemoryBlock('5', 300),
      new MemoryBlock('6', 400),
      new MemoryBlock('7', 500),
      new MemoryBlock('8', 500),
      new MemoryBlock('9', 300),
      new MemoryBlock('10', 200),
      new MemoryBlock('11', 50),
    ];
    $start = array_reduce($blocks, static function (int $total, MemoryBlock $block) {
      return $total + $block->getSize();
    }, 0);
    $processes = [
      new Process(112, 3),
      new Process(212),
      new Process(417, 2),
      new Process(426),
      new Process(70, 3),
    ];

    foreach ($this->permutations($processes) as $processList) {
      $blockList = $blocks;
      array_walk($processList, static function (&$item) use ($processes) {
        $item = $processes[$item];
      });
      $this->allocateBestFit4($blockList, $processList);
      $end = array_reduce($blockList, static function (int $total, MemoryBlock $block) {
        return $total + $block->getSize();
      }, 0);
      VarDumper::dump(sprintf('Order: %s', implode(' - ', $processList)));
      VarDumper::dump(sprintf('Used: %s%%', (1 - ($end / $start)) * 100));
    }

//    // First Fit.
//    $blocks = [
//      new MemoryBlock('1', 25),
//      new MemoryBlock('2', 50),
//      new MemoryBlock('3', 100),
//      new MemoryBlock('4', 200),
//      new MemoryBlock('5', 300),
//      new MemoryBlock('6', 400),
//      new MemoryBlock('7', 500),
//      new MemoryBlock('8', 500),
//      new MemoryBlock('9', 300),
//      new MemoryBlock('10', 200),
//      new MemoryBlock('11', 50),
//    ];
//    $start = array_reduce($blocks, static function (int $total, MemoryBlock $block) {
//      return $total + $block->getSize();
//    }, 0);
//    $processes = [
//      new Process(112, 3),
//      new Process(212),
//      new Process(417, 2),
//      new Process(426),
//      new Process(70, 3),
//    ];
//
//    $this->allocateFirstFit($blocks, $processes);
//    $end = array_reduce($blocks, static function (int $total, MemoryBlock $block) {
//      return $total + $block->getSize();
//    }, 0);
//    VarDumper::dump('First fit');
//    VarDumper::dump($start);
//    VarDumper::dump($end);
//    VarDumper::dump(($end / $start));
//    VarDumper::dump(sprintf('Used: %s%%', (1 - ($end / $start)) * 100));
//
//    // Worst Fit.
//    $blocks = [
//      new MemoryBlock('1', 25),
//      new MemoryBlock('2', 50),
//      new MemoryBlock('3', 100),
//      new MemoryBlock('4', 200),
//      new MemoryBlock('5', 300),
//      new MemoryBlock('6', 400),
//      new MemoryBlock('7', 500),
//      new MemoryBlock('8', 500),
//      new MemoryBlock('9', 300),
//      new MemoryBlock('10', 200),
//      new MemoryBlock('11', 50),
//    ];
//    $start = array_reduce($blocks, static function (int $total, MemoryBlock $block) {
//      return $total + $block->getSize();
//    }, 0);
//    $processes = [
//      new Process(112, 3),
//      new Process(212),
//      new Process(417, 2),
//      new Process(426),
//      new Process(70, 3),
//    ];
//
//    $this->allocateWorstFit($blocks, $processes);
//    $end = array_reduce($blocks, static function (int $total, MemoryBlock $block) {
//      return $total + $block->getSize();
//    }, 0);
//    VarDumper::dump('Worst fit');
//    VarDumper::dump($start);
//    VarDumper::dump($end);
//    VarDumper::dump(($end / $start));
//    VarDumper::dump(sprintf('Used: %s%%', (1 - ($end / $start)) * 100));
//
//    // Best Fit.
//    $blocks = [
//      new MemoryBlock('1', 150),
//      new MemoryBlock('2', 350),
//    ];
//    $start = array_reduce($blocks, static function (int $total, MemoryBlock $block) {
//      return $total + $block->getSize();
//    }, 0);
//    $processes = [
//      new Process(300),
//      new Process(25),
//      new Process(125),
//      new Process(50),
//    ];
//
//    $this->allocateBestFit4($blocks, $processes);
//    $end = array_reduce($blocks, static function (int $total, MemoryBlock $block) {
//      return $total + $block->getSize();
//    }, 0);
//    VarDumper::dump('Best fit');
//    VarDumper::dump($start);
//    VarDumper::dump($end);
//    VarDumper::dump(($end / $start));
//    VarDumper::dump(sprintf('Used: %s%%', (1 - ($end / $start)) * 100));

//    $processes = [
//      new Process(212),
//      new Process(417),
//      new Process(112, 4),
//      new Process(426),
//    ];
//
//    $blocks = [
//      new MemoryBlock('1', 100),
//      new MemoryBlock('2', 500),
//      new MemoryBlock('3', 200),
//      new MemoryBlock('4', 300),
//      new MemoryBlock('5', 600),
//    ];
//
//    $this->allocateBestFit3($blocks, $processes);
//    VarDumper::dump($blocks);
//    VarDumper::dump($blocks);
//    VarDumper::dump($this->allocateBestFit(19, $blocks));
//    VarDumper::dump($this->allocateBestFit(5, $blocks));
//    VarDumper::dump($this->allocateBestFit(1, $blocks));
  }

  public function permutations(array $elements = [], array &$keys = []) {
    foreach ($elements as $key => $value) {
      if (in_array($key, $keys)) {
        continue;
      }

      $keys[] = $key;

      if (count($keys) === count($elements)) {
        yield $keys;
      }
      else {
        foreach ($this->permutations($elements, $keys) as $subValue) {
          yield $subValue;
        }
      }

      array_pop($keys);
    }
  }

  /**
   * @param \Drupal\influxdb\Commands\MemoryBlock[] $blocks
   * @param \Drupal\influxdb\Commands\Process[] $processes
   *
   * @return void
   */
  protected function allocateBestFit4(array &$blocks, array $processes) {
    $processBlocks = collect($blocks);

    foreach ($processes as $process) {
      // Create sliding windows per process cycle, and filter them by process
      // size.
      $windows = $processBlocks
        ->sliding($process->getCycles())
        ->filter(fn (Collection $window) => $window->every(fn (MemoryBlock $block) => $block->getSize() >= $process->getSize()));

      if ($windows->isEmpty()) {
        // There are no windows available for the process.
        continue;
      }

      // Create a queue, where the priority is the negative sum of the blocks.
      $queue = array_reduce($windows->toArray(), static function (\SplPriorityQueue $queue, array $window) {
        $total = array_reduce($window, static function (int $total, MemoryBlock $block) {
          return $total + $block->getSize();
        }, 0);
        $queue->insert($window, -$total);

        return $queue;
      }, new \SplPriorityQueue());

      // Get the item at the top, it matches the process restrictions as close
      // as possible (combination of size and cycles).
      /** @var \Drupal\influxdb\Commands\MemoryBlock[] $window */
      $window = $queue->extract();

      foreach ($window as &$block) {
        $block->allocate($process);
        $block->setSize($block->getSize() - $process->getSize());
      }
    }
  }

  /**
   * @param \Drupal\influxdb\Commands\MemoryBlock[] $blocks
   * @param \Drupal\influxdb\Commands\Process[] $processes
   *
   * @return void
   */
  protected function allocateFirstFit(array &$blocks, array $processes) {
    $processBlocks = collect($blocks);

    foreach ($processes as $process) {
      // Create sliding windows per process cycle, and filter them by process
      // size.
      $windows = $processBlocks
        ->sliding($process->getCycles())
        ->filter(fn (Collection $window) => $window->every(fn (MemoryBlock $block) => $block->getSize() >= $process->getSize()));

      if ($windows->isEmpty()) {
        // There are no windows available for the process.
        continue;
      }

      // Get the first item, it matches the process restrictions as close
      // as possible (combination of size and cycles).
      /** @var \Drupal\influxdb\Commands\MemoryBlock[] $window */
      $window = $windows->first();

      foreach ($window as &$block) {
        $block->allocate($process);
        $block->setSize($block->getSize() - $process->getSize());
      }
    }
  }

  /**
   * @param \Drupal\influxdb\Commands\MemoryBlock[] $blocks
   * @param \Drupal\influxdb\Commands\Process[] $processes
   *
   * @return void
   */
  protected function allocateWorstFit(array &$blocks, array $processes) {
    $processBlocks = collect($blocks);

    foreach ($processes as $process) {
      // Create sliding windows per process cycle, and filter them by process
      // size.
      $windows = $processBlocks
        ->sliding($process->getCycles())
        ->filter(fn (Collection $window) => $window->every(fn (MemoryBlock $block) => $block->getSize() >= $process->getSize()));

      if ($windows->isEmpty()) {
        // There are no windows available for the process.
        continue;
      }

      // Create a queue, where the priority is the sum of the blocks.
      $queue = array_reduce($windows->toArray(), static function (\SplPriorityQueue $queue, array $window) {
        $total = array_reduce($window, static function (int $total, MemoryBlock $block) {
          return $total + $block->getSize();
        }, 0);
        $queue->insert($window, $total);

        return $queue;
      }, new \SplPriorityQueue());

      // Get the item at the top, it matches the process restrictions as close
      // as possible (combination of size and cycles).
      /** @var \Drupal\influxdb\Commands\MemoryBlock[] $window */
      $window = $queue->extract();

      foreach ($window as &$block) {
        $block->allocate($process);
        $block->setSize($block->getSize() - $process->getSize());
      }
    }
  }

  ///////
  ///
  ///
  ///
  ///
  ///

  /**
   * @param \Drupal\influxdb\Commands\MemoryBlock[] $blocks
   * @param \Drupal\influxdb\Commands\Process[] $processes
   *
   * @return void
   */
  protected function allocateBestFit2(array &$blocks, array $processes) {
    $queue = array_reduce($blocks, static function (\SplPriorityQueue $queue, MemoryBlock $block) {
      if ($block->isUnused()) {
        $queue->insert($block, -$block->getSize());
      }

      return $queue;
    }, new \SplPriorityQueue());

    foreach ($processes as $i => $process) {
      // Find the smallest block that fit the process.
      /** @var \Drupal\influxdb\Commands\MemoryBlock|NULL $block */
      $block = NULL;
      $pq = new \SplPriorityQueue();
      while (!$queue->isEmpty()) {
        /** @var \Drupal\influxdb\Commands\MemoryBlock $currentBlock */
        $currentBlock = $queue->extract();
        if ($currentBlock->getSize() >= $process->getSize()) {
          $block = $currentBlock;
          break;
        }
        $pq->insert($currentBlock, -$currentBlock->getSize());
      }

      if (is_null($block)) {
        // Couldn't allocate this process to any block.
        break;
      }

      // Allocate the process to the block.
      $block->allocate($process);
      $block->setSize($block->getSize() - $process->getSize());
      $allocation[$i] = $block;

      // Re-insert the block into the priority queue, with its new size
      $queue->insert($block, -$block->getSize());
      while (!$pq->isEmpty()) {
        /** @var \Drupal\influxdb\Commands\MemoryBlock $pqBlock */
        $pqBlock = $pq->extract();
        $queue->insert($pqBlock, -$pqBlock->getSize());
      }
    }
  }

  /**
   * @param \Drupal\influxdb\Commands\MemoryBlock[] $blocks
   * @param \Drupal\influxdb\Commands\Process[] $processes
   *
   * @return void
   */
  protected function allocateBestFit3(array &$blocks, array $processes) {
    foreach ($processes as $process) {
      /** @var \Drupal\influxdb\Commands\MemoryBlock|NULL $bestBlock */
      $bestBlock = NULL;
      foreach ($blocks as $block) {
        if ($block->getSize() >= $process->getSize() && (is_null($bestBlock) || $block->getSize() < $bestBlock->getSize())) {
          $bestBlock = $block;
        }
      }

      if (is_null($bestBlock)) {
        continue;
      }

      $bestBlock->allocate($process);
      $bestBlock->setSize($bestBlock->getSize() - $process->getSize());
    }
  }

  /**
   * @param $size
   * @param \Drupal\influxdb\Commands\MemoryBlock[] $blocks
   */
  protected function allocateBestFit($size, array &$blocks) {
    $bestFit = NULL;
    $bestSizeDiff = PHP_INT_MAX;

    foreach ($blocks as &$block) {
      if (!$block->isFree() || $block->getSize() >= $size) {
        continue;
      }

      $sizeDiff = $block->getSize() - $size;
      if ($sizeDiff < $bestSizeDiff) {
        $bestFit = $block;
        $bestSizeDiff = $sizeDiff;
      }
    }

    if (is_null($bestFit)) {
      throw new \Exception('Could not allocate');
    }

    $bestFit->toggleFree();

    return $bestFit;
  }

  protected function testRefactorFromC() {
    $appliances = [];
    $appliances[] = [
      'schedules_per_week' => 2,
      'rating' => 2000,
      'duty_cycle' => 2,
    ];
    $appliances[] = [
      'schedules_per_week' => 4,
      'rating' => 1500,
      'duty_cycle' => 4,
    ];

    $energy = [];
//    $energy[] = ['energy' => 200, 'time']

    $recommendations = $this->allocateBestFitFromC($appliances, $energy);
  }

  /**
   * Allocate best fit
   *
   * @param array $appliances
   * @param array $energy
   *
   * @return void
   */
  protected function allocateBestFitFromC(array $appliances, array &$energy) {
    $recommendations = [];
    foreach ($appliances as $appliance) {
      for ($i = 0; $i < $appliance['schedules_per_week']; $i++) {
        $best_pos = null;
        $max_area = 0;
        $cur_area = 0;

        $first_time = null;
        $last_time = null;

        // Check if there is a fit and if so, save the first one.
        foreach ($energy as $time_and_energy) {
          if ($time_and_energy['energy'] < $appliance['rating']) {
            $cur_area = 0;
            $first_time = null;
          } else {
            if ($first_time === null) {
              $cur_area += $time_and_energy['energy'] - $appliance['rating'];
              $first_time = $time_and_energy['time'];
            }
            if ($time_and_energy['time'] - $first_time < date_interval_create_from_date_string("{$appliance['duty_cycle']} hours")) {
              $cur_area += $time_and_energy['energy'] - $appliance['rating'];
            } elseif ($time_and_energy['time'] - $first_time == date_interval_create_from_date_string("{$appliance['duty_cycle']} hours")) {
              $cur_area += $time_and_energy['energy'] - $appliance['rating'];
              $last_time = $time_and_energy['time'];

              // If this is a better fit than the previous one, save it.
              if ($cur_area > $max_area) {
                $max_area = $cur_area;
                $best_pos = $first_time;
              }

              break;
            }
          }
        }

        // Allocate the best fit.
        $it_low = array_filter($energy, function ($e) use ($best_pos) {
          return $e['time'] >= $best_pos;
        });
        $it_up = array_filter($energy, function ($e) use ($best_pos, $appliance) {
          return $e['time'] <= $best_pos + date_interval_create_from_date_string("{$appliance['duty_cycle']} hours");
        });
        foreach ($it_low as &$it) {
          $it['energy'] -= $appliance['rating'];
        }
        foreach ($it_up as &$it) {
          $it['energy'] -= $appliance['rating'];
        }

        $task = [
          'id' => 0,
          'name' => '',
          'start_time' => $best_pos,
          'end_time' => $best_pos + date_interval_create_from_date_string("{$appliance['duty_cycle']} hours"),
          'auto_profile' => 0,
          'is_user_declared' => false,
          'appliances' => [$appliance['id']],
        ];
        $recommendations[] = $task;
      }
    }

    return $recommendations;
  }

}
