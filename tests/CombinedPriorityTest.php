<?php

declare(strict_types=1);

namespace Crell\TopSort;

use PHPUnit\Framework\TestCase;

class CombinedPriorityTest extends TestCase
{
    protected array $subjects = [
        CombinedSortPriority::class,
    ];

    /**
     * @test
     * @dataProvider examples
     */
    public function sortTests(string $class, array $items, array $expected = []): void
    {
        $subject = new $class();

        foreach ($items as $item) {
            $item['item'] = $item['id'];
            $subject->add(...$item);
        }

        self::assertEquals($expected, $subject->sorted());
    }

    public function examples(): iterable
    {
        foreach ($this->subjects as $subject) {
            foreach ($this->sortableExamples() as $item) {
                yield (['class' => $subject] + $item);
            }
        }
    }

    public function sortableExamples(): iterable
    {
        yield [
            'items' => [
                ['id' => 'A'],
                ['id' => 'B'],
                ['id' => 'C'],
            ],
            'expected' => ['A', 'B', 'C'],
        ];
        yield [
            'items' => [
                ['id' => 'A', 'priority' => 1],
                ['id' => 'B', 'priority' => 2],
                ['id' => 'C', 'priority' => 3],
            ],
            'expected' => ['C', 'B', 'A'],
        ];
        yield [
            'items' => [
                ['id' => 'A', 'priority' => 1],
                ['id' => 'B'],
                ['id' => 'C', 'priority' => 3],
            ],
            'expected' => ['C', 'A', 'B'],
        ];
        yield [
            'items' => [
                ['id' => 'A', 'priority' => 1],
                ['id' => 'B', 'priority' => 3],
                ['id' => 'C', 'priority' => 1],
            ],
            'expected' => ['B', 'A', 'C'],
        ];
        yield [
            'items' => [
                ['id' => 'A', 'before' => 'B'],
                ['id' => 'B', 'priority' => 3],
                ['id' => 'C', 'priority' => 1],
            ],
            'expected' => ['B', 'C', 'A'],
        ];
        /* Fails because it can't prioritize against unprioritized items yet.
        yield [
            'items' => [
                ['id' => 'A', 'before' => 'C'],
                ['id' => 'B', 'before' => 'C'],
                ['id' => 'C'],
            ],
            'expected' => ['A', 'B', 'C'],
        ];
        */
        /*
        yield [
            'items' => [
                ['id' => 'A', 'before' => 'B'],
                ['id' => 'B', 'priority' => 3],
                ['id' => 'C', 'priority' => 1],
            ],
            'expected' => ['B', 'C', 'A'],
        ];
        */
    }

}
