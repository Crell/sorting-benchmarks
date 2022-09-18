<?php

declare(strict_types=1);

namespace Crell\TopSort\Benchmarks;

use Crell\TopSort\Sorter;
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
abstract class SortCase
{
    protected const Prefix = 'A';

    protected const DataSize = 50000;

    protected const CommonPriority = 5;

    protected const RandomPriorityMax = self::DataSize;

    protected Sorter $sorter;

    public function tearDown(): void {}

    /**
     * This is the only actual benchmark code.
     *
     * Everything else is just different subclasses with their own setup routines.
     */
    public function benchSort(): void
    {
        // Since the implementations have caching, we need to
        // clone the sorter before sorting so that it's freshly
        // not-sorted next time.
        $result = (clone($this->sorter))->sorted();
    }
}
