<?php

namespace Tests;

use App\Models\Pieces\Factory;
use PHPUnit\Framework\TestCase;


abstract class PieceTest extends TestCase
{
    protected $piece;
    protected ?array $board;
    protected int $x = 3;
    protected int $y = 4;


    protected function setUp(): void
    {
        $this->board = [];
        for ($row = 0; $row < 8; ++$row) {
            for ($col = 0; $col < 8; ++$col) {
                $this->board[$row][$col] = null;
            }
        }
    }


    protected function tearDown(): void
    {
        $this->piece = null;
        $this->board = null;
    }


    public function testGetColor(): void
    {
        $this->assertSame(0, $this->piece->getColor());
    }


    public function testGetCoords(): void
    {
        $this->assertSame([3, 4], $this->piece->getCoords());
    }


    public function testSetCoords(): void
    {
        $this->piece->setCoords(6, 5);
        $this->assertSame([6, 5], $this->piece->getCoords());
    }


    public function testGetMovesCounter(): void
    {
        $this->assertSame(0, $this->piece->getMovesCounter());
    }


    public function testIncrementMovesCounter(): void
    {
        $movesCounter = $this->piece->getMovesCounter();
        $this->piece->incrementMovesCounter();
        $this->assertSame($movesCounter + 1, $this->piece->getMovesCounter());
    }


    public function testForward(): void
    {
        $this->assertSame($this->piece->getColor() ? -1 : 1, $this->piece->forward());
        $this->assertSame($this->piece->getColor() ? -2 : 2, $this->piece->forward(2));
    }


    public function testJsonSerialize(): void
    {
        $factory = new Factory();
        $json = [
            'type' => $factory->getLetter($this->piece),
            'moveCounter' => 0
        ];
        $this->assertSame(
            true,
            count(array_intersect($json, $this->piece->jsonSerialize())) === count($json)
        );
    }

    /**
     * @dataProvider coordsProvider
     * @param int|null $x
     * @param int|null $y
     * @param int|null $expected
     */
    function testCheckMove(int $x = null, int $y = null, int $expected = null): void
    {
        $this->assertSame($expected, $this->piece->checkMove($x, $y, $this->board, 0));
    }


    abstract function coordsProvider(): array;
}
