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

    protected const DataSize = 100_000;

    protected const CommonPriority = 5;

    protected Sorter $sorter;

    public function tearDown(): void {}

    /**
     * This is the only actual benchmark code.
     *
     * Everything else is just different subclasses with their own setup routines.
     */
    public function benchSort(): void
    {
        $result = $this->sorter->sorted();
    }
}