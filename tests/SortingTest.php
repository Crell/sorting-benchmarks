<?php

declare(strict_types=1);

namespace Crell\TopSort;


use PHPUnit\Framework\TestCase;

class SortingTest extends TestCase
{
    protected array $subjects = [
        TopSortBasic::class,
//        TopSortInternal::class,
    ];

    protected array $noCycleSafetySubjects = [
        TopSortInternal::class,
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