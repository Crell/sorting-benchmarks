<?php

declare(strict_types=1);

namespace Crell\TopSort;


use PHPUnit\Framework\TestCase;

class TopSortTest extends TestCase
{
    protected array $subjects = [
        TopSortBasic::class,
    ];

    protected array $noCycleSafetySubjects = [
        //TopSortInternal::class,
    ];

    /**
     * @test
     * @dataProvider examples
     */
    public function sortTests(string $class, array $items, array $expected = [], bool $cycleExpected = false): void
    {
        if ($cycleExpected) {
            $this->expectException(CycleFound::class);
        }

        $subject = new $class();

        foreach ($items as $item) {
            $item['item'] = $item['id'];
            $subject->add(...$item);
        }

        self::assertEquals($expected, $subject->sorted());
    }

    public function examples(): iterable
    {
        $data = function () {
            yield from $this->sortableExamples();
            yield from $this->unsortableExamples();
        };

        foreach ($this->subjects as $subject) {
            foreach ($data() as $item) {
                yield (['class' => $subject] + $item);
            }
        }

        foreach ($this->noCycleSafetySubjects as $subject) {
            foreach ($this->sortableExamples() as $item) {
                yield (['class' => $subject] + $item);
            }
        }
    }


    public function sortableExamples(): iterable
    {
        yield [
            'items' => [
                ['id' => 'A', 'before' => 'C'],
                ['id' => 'B',],
                ['id' => 'C', 'before' => 'B'],
            ],
            'expected' => ['A', 'C', 'B'],
        ];
        yield [
            'items' => [
                ['id' => 'A', 'before' => 'C'],
                ['id' => 'B', 'before' => 'C'],
                ['id' => 'C'],
            ],
            'expected' => ['A', 'B', 'C'],
        ];

        // The following two are identical, but reverse specified.
        // These two both fail for TopSortInternal. A and B are mis-ordered.
        yield [
            'items' => [
                ['id' => 'A'],
                ['id' => 'B'],
                ['id' => 'C', 'before' => 'A'],
                ['id' => 'D', 'before' => 'C'],
                ['id' => 'E', 'before' => ['B', 'D']],
            ],
            'expected' => ['E', 'D', 'C', 'A', 'B'],
        ];
        yield [
            'items' => [
                ['id' => 'A', 'after' => 'C'],
                ['id' => 'B', 'after' => 'E'],
                ['id' => 'C', 'after' => 'D'],
                ['id' => 'D', 'after' => 'E'],
                ['id' => 'E'],
            ],
            'expected' => ['E', 'D', 'C', 'A', 'B'],
        ];
    }

    public function unsortableExamples(): iterable
    {
        yield [
            'items' => [
                ['id' => 'A', 'before' => 'A'],
            ],
            'expected' => [],
            'cycleExpected' => true,
        ];
        yield [
            'items' => [
                ['id' => 'A', 'before' => 'B'],
                ['id' => 'B', 'before' => 'A'],
            ],
            'expected' => [],
            'cycleExpected' => true,
        ];
        yield [
            'items' => [
                ['id' => 'A', 'before' => 'B'],
                ['id' => 'B', 'before' => 'C'],
                ['id' => 'C', 'before' => 'A'],
            ],
            'expected' => [],
            'cycleExpected' => true,
        ];
    }

}