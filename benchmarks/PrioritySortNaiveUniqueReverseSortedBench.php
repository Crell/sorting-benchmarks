<?php

declare(strict_types=1);

namespace Crell\TopSort\Benchmarks;

use Crell\TopSort\PrioritySortNaive;
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
class PrioritySortNaiveUniqueReverseSortedBench extends SortCase
{
    public function setUp(): void
    {
        $this->sorter = new PrioritySortNaive();

        for ($i = self::DataSize; $i > 0; --$i) {
            $this->sorter->add(
                item: self::Prefix . $i,
                id: self::Prefix . $i,
                priority: $i,
            );
        }
    }
}
