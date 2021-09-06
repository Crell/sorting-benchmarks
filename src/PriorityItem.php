<?php

declare(strict_types=1);

namespace Crell\TopSort;

class PriorityItem
{
    public function __construct(
        public string $id,
        public mixed $item,
        public int $priority = 0,
    ) {}
}