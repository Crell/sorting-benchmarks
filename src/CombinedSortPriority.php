<?php

declare(strict_types=1);

namespace Crell\TopSort;

/**
 * Fold topologically specified items to a priority, then sort by priority.
 */
class CombinedSortPriority implements Sorter
{
    /** @var array<int, <string, <CombinedItem>> */
    protected array $items;

    /** @var CombinedItem[] */
    protected array $toPrioritize = [];

    /** @var array<string, CombinedItem>  */
    protected array $itemIndex = [];

    protected bool $sorted = false;

    public function add(mixed $item, ?string $id = null, ?int $priority = 0, string|array|null $before = null, string|array|null $after = null): string
    {
        $id = $this->enforceUniqueId($id);

        if ($before || $after) {
            $before ??= [];
            if (is_string($before)) {
                $before = [$before];
            }
            $after ??= [];
            if (is_string($after)) {
                $after = [$after];
            }
            $record = new CombinedItem(id: $id, item: $item, before: $before, after: $after);
            $this->toPrioritize[$id] = $record;
            $this->itemIndex[$id] = $record;
            return $id;
        }

        $record = new CombinedItem(id: $id, item: $item, priority: $priority);
        $this->items[$priority][$id] = $record;
        $this->itemIndex[$id] = $record;
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
                yield from array_map(static fn (CombinedItem $item): mixed => $item->item, $itemList);
            }
        })();

        return iterator_to_array($it, false);
    }

    protected function sort(): void
    {
        $this->normalizeDirection();

        // Convert any before/after items to priorities.
        $this->prioritizePendingItems();

        if (!$this->sorted) {
            krsort($this->items);
            $this->sorted = true;
        }
    }

    protected function prioritizePendingItems(): void
    {
        // @todo This may still be buggy. If the item comes before/after
        // an item that isn't yet prioritized, it won't have a priority
        // yet to calculate from.  Ugh, does this have to be recursive?

        // If there are no prioritized items at all yet, pick one
        // and declare it priority 0. (The last one is the fastest
        // to access and remove from the list, so do that.)
        if (empty($this->items)) {
            $item = array_pop($this->toPrioritize);
            $this->items[$item->priority][$item->id] = $item;
        }

        foreach ($this->toPrioritize as $item) {
            // Get the priority of all of the items this one must come before.
            $otherPriorities = array_map(fn(string $before): int => $this->itemIndex[$before]->priority, $item->before);

            // This seems backwards, but that's because we want higher numbers
            // to come first.  So something that comes "before" another item
            // needs to have a higher priority than it.
            $priority = max($otherPriorities) + 1;

            $item->priority = $priority;
            $this->items[$priority][] = $item;
        }

        // We never need to reprioritize these again.
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

    /**
     * Convert all records to use `before`, not `after`, for consistency.
     */
    protected function normalizeDirection(): void
    {
        foreach ($this->toPrioritize as $node) {
            foreach ($node->after ?? [] as $afterId) {
                // If this item should come after something that doesn't exist,
                // that's the same as no restrictions.
                if ($this->itemIndex[$afterId]) {
                    $this->itemIndex[$afterId]->before[] = $node->id;
                }
            }
        }
    }
}
