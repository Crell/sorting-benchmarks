<?php

declare(strict_types=1);

namespace Crell\TopSort;

use Traversable;

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
class PrioritySortGrouped implements \IteratorAggregate
{
    /** @var array<int, <string, <PriorityItem>> */
    protected array $items;

    protected bool $sorted = false;

    public function add(mixed $item, ?string $id = null, int $priority = 0): string
    {
        $id = $this->enforceUniqueId($id);

        $this->items[$priority][$id] = new PriorityItem(id: $id, item: $item, priority: $priority);
        $this->sorted = false;
        return $id;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->sorted());
    }

    public function sorted(): iterable
    {
        $this->sort();

        $it = (function () {
            foreach ($this->items as $itemList) {
                yield from array_map(static function (PriorityItem $item) {
                    return $item->item;
                }, $itemList);
            }
        })();

        return iterator_to_array($it, false);
    }

    protected function sort(): void
    {
        if (!$this->sorted) {
            krsort($this->items);
            $this->sorted = true;
        }
    }

    /**
     * Ensures a unique ID for all items in the collection.
     *
     * @param string|null $id
     *   The proposed ID of an item, or null to generate a random string.
     *
     * @return string
     *   A confirmed unique ID string.
     */
    protected function enforceUniqueId(?string $id): string
    {
        $candidateId = $id ?? uniqid('', true);

        $counter = 1;
        while (isset($this->itemLookup[$candidateId])) {
            $candidateId = $id . '-' . $counter++;
        }

        return $candidateId;
    }
}
