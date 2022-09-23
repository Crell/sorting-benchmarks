<?php

declare(strict_types=1);

namespace Crell\TopSort\Benchmarks;

use Crell\TopSort\CombinedSortPriority;
use PhpBench\Benchmark\Metadata\Annotations\AfterMethods;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\OutputTimeUnit;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;

/**
 * @Revs(10)
 * @Iterations(3)
 * @Warmup(2)
 * @BeforeMethods({"setUp"})
 * @AfterMethods({"tearDown"})
 * @OutputTimeUnit("milliseconds", precision=3)
 */
class CombinedSortPriorityMixedRandomDataBench extends SortCase
{
    public function setUp(): void
    {
        $this->sorter = new CombinedSortPriority();

        for ($i = 0; $i < self::DataSize; ++$i) {
            if ($i % 2) {
                $this->sorter->add(
                    item: self::Prefix . $i,
                    id: self::Prefix . $i,
                    priority: \random_int(0, self::RandomPriorityMax),
                );
            }
            $this->sorter->add(
                item: self::Prefix . $i,
                id: self::Prefix . $i,
                before: $i === 0 ? null : (self::Prefix . \random_int(0, $i - 1)),
            );
        }
    }
}
