<?php

declare(strict_types=1);

namespace Crell\TopSort;


/**
 * A smarter, grouped sorting mechanism.
 *
 * In this version, items are added to a list keyed first by the priority.
 * Then just the smaller, priority list is sorted, which makes for a much
 * smaller list to sort.  Each sub-list is already in order as they were
 * added, which is what we want.
 *
 * The generator used to flatten everything out may be inefficient. I'm
 * not sure on that front.
 */
interface Sorter extends \IteratorAggregate
{
    public function getIterator(): \ArrayIterator;

    public function sorted(): iterable;
}