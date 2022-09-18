<?php

declare(strict_types=1);

namespace Crell\TopSort;

use Traversable;

/**
 * Fold prioritized items to a topological graph before sorting topologically.
 *
 * This works, but with a caveat.
 *
 * If two or more items are equally sortable (eg, both have no incoming arrows),
 * they will be returned in reverse order of how they were originally added. That
 * is technically correct, but unexpected.  Turning that around may have performance
 * concerns, but is handled by the array_reverse() call in sort().
 */
class CombinedSortTop implements Sorter
{
    /** @var array<string, TopologicalItem>  */
    protected array $items = [];

    /** @var CombinedItem[] */
    protected array $toPrioritize = [];

    protected ?array $sorted = null;

    public function add(mixed $item, ?string $id = null, ?int $priority = null, string|array|null $before = null, string|array|null $after = null): string
    {
        $id = $this->enforceUniqueId($id);

        $before ??= [];
        if (is_string($before)) {
            $before = [$before];
        }
        $after ??= [];
        if (is_string($after)) {
            $after = [$after];
        }

        if (!is_null($priority)) {
            $record = new CombinedItem(id: $id, item: $item, before: $before, after: $after, priority: $priority);
            $this->toPrioritize[$priority][$id] = $record;
            $this->sorted = null;
            return $id;
        }

        $this->items[$id] = new CombinedItem(id: $id, item: $item, before: $before, after: $after);
        $this->sorted = null;
        return $id;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->sorted());
    }

    public function sorted(): iterable
    {
        return $this->sorted ??= array_map(fn (string $id): mixed => $this->items[$id]->item, $this->sort());
    }

    protected function sort(): array
    {
        $this->prioritizePendingItems();

        // First, convert all records to use `before`, not `after`, for consistency.
        foreach ($this->items as $node) {
            foreach ($node->after ?? [] as $afterId) {
                // If this item should come after something that doesn't exist,
                // that's the same as no restrictions.
                if ($this->items[$afterId]) {
                    $this->items[$afterId]->before[] = $afterId;
                }
            }
        }

        // Compute the initial indegrees for all items.
        $indegrees = array_fill_keys(array_keys($this->items), 0);
        foreach ($this->items as $id => $node) {
            foreach ($node->before as $neighbor) {
                //$indegrees[$neighbor] ??= 0;
                $indegrees[$neighbor]++;
            }
        }

        // Find items with nothing that comes before it.
        $usableItems = [];
        foreach ($this->items as $id => $item) {
            if ($indegrees[$id] === 0) {
                $usableItems[] = $id;
            }
        }

        // Because the items were pushed onto the usable list, we need
        // to reverse it to get them back in the order they were added.
        $usableItems = array_reverse($usableItems);

        // Keep removing usable items until there are none left.
        $sorted = [];
        while (count($usableItems)) {
            // Grab an available item. We know it's sorted.
            $id = array_pop($usableItems);
            $sorted[] = $id;

            // Decrement the neighbor count of everything that item was before.
            foreach ($this->items[$id]->before as $neighbor) {
                $indegrees[$neighbor]--;
                if ($indegrees[$neighbor] === 0) {
                    $usableItems[] = $neighbor;
                }
            }
        }

        // We've run out of nodes with no incoming edges.
        // Did we add all the nodes or find a cycle?
        if (count($sorted) === count($this->items)) {
            return $sorted;
        }

        throw new CycleFound();
    }

    protected function prioritizePendingItems(): void
    {
        foreach ($this->toPrioritize as $priority => $items) {
            /** @var CombinedItem $item */
            foreach ($items as $item) {
                /**
                 * @var int $otherPriority
                 * @var array $otherItems */
                foreach ($this->toPrioritize as $otherPriority => $otherItems) {
                    // For every other pending item, if this item's priority is
                    // greater than it, that means it must come before it. Mark
                    // the item as such.
                    if ($priority > $otherPriority) {
                        $add = array_map(static fn(CombinedItem $i) => $i->id, $otherItems);
                        $item->before = [...$item->before, ...array_values($add)];
                    }
                    $this->items[$item->id] = $item;
                }
            }
        }

        // We don't need to reprioritize these again.
        $this->toPrioritize = [];
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
