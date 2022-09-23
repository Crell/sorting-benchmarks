<?php

declare(strict_types=1);

namespace Crell\TopSort\Benchmarks;

use Crell\TopSort\TopSortBasic;
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
class TopSortBasicRandomSequenceAfterBench extends SortCase
{
    public function setUp(): void
    {
        $this->sorter = new TopSortBasic();

        for ($i = 0; $i < self::DataSize; ++$i) {
            $this->sorter->add(
                item: self::Prefix . $i,
                id: self::Prefix . $i,
                after: $i === self::DataSize-1 ? null : (self::Prefix . \random_int($i+1, self::DataSize - 1)),
            );
        }
    }
}