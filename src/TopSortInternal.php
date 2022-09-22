<?php

declare(strict_types=1);

namespace Crell\TopSort;

use Traversable;

/**
 * This version does not work correctly.
 *
 * This approach uses an unusual usort() approach, comparing based on the 'before'
 * property.  Unfortunately, it breaks down in some cases and does not properly sort,
 * seemingly because it does not compare every pair of items.
 *
 * See the TopSortTest class for examples of test cases that fail with this approach.
 *
 * However, because it lacks cycle detection
 * the results for a cyclic graph will always be incorrect in some way. Some requirement
 * will not be met, and thus while a result is returned, it is not properly ordered
 * (as there is no proper order).
 */
class TopSortInternal implements Sorter
{
    /** @var array<string, TopologicalItem>  */
    protected array $items;

    protected ?array $sorted = null;

    public function add(mixed $item, ?string $id = null, string|array|null $before = null, string|array|null $after = null): string
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

        $this->items[$id] = new TopologicalItem(id: $id, item: $item, before: $before, after: $after);
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
       $this->normalizeDirection();

        uasort($this->items, static function (TopologicalItem $left, TopologicalItem $right): int {
            if (in_array($right->id, $left->before, true)) {
                return -1;
            }
            if (in_array($left->id, $right->before, true)) {
                return 1;
            }
            return 0;
        });

        return array_keys($this->items);
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
        foreach ($this->items as $node) {
            foreach ($node->after ?? [] as $afterId) {
                // If this item should come after something that doesn't exist,
                // that's the same as no restrictions.
                if ($this->items[$afterId]) {
                    $this->items[$afterId]->before[] = $node->id;
                }
            }
        }
    }
}
