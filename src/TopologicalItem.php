<?php

declare(strict_types=1);

namespace Crell\TopSort;

class TopologicalItem
{
    public function __construct(
        public string $id,
        public mixed $item,
        public array $before = [],
        public array $after = [],
    ) {}
}
