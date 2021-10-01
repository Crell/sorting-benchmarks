<?php

declare(strict_types=1);

namespace Crell\TopSort;

use Traversable;

/**
 * A basic naive sorting based on priority.
 *
 * The priority is an integer on each item.  Just usort() and be done with it.
 */
class PrioritySortNaive implements \IteratorAggregate
{
    /** @var array<string, PriorityItem>  */
    protected array $items;

    protected bool $sorted = false;

    public function add(mixed $item, ?string $id = null, int $priority = 0): string
    {
        $id = $this->enforceUniqueId($id);

        $this->items[$id] = new PriorityItem(id: $id, item: $item, priority: $priority);
        $this->sorted = false;
        return $id;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->sorted());
    }

    public function sorted(): iterable
    {
        if (!$this->sorted) {
            $this->sort();
            $this->sorted = true;
        }
        return array_map(static fn (PriorityItem $item): mixed => $item->item, $this->items);
    }

    protected function sort(): array
    {
        // We want higher numbers to come first, hence the * -1 to invert it.
        $sort = static fn (PriorityItem $a, PriorityItem $b): int => -1 * ($a->priority <=> $b->priority);

        usort($this->items, $sort);

        return $this->items;
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
