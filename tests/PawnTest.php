<?php

namespace Tests;

use App\Models\Pieces\Factory;
use App\Models\Pieces\Pawn;
use App\Models\Pieces\Piece;


class PawnTest extends PieceTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->piece = new Pawn(0, $this->x, $this->y, 0, 0);
        $this->board[$this->y][$this->x] = $this->piece;
    }


    public function testCheckMove(int $x = null, int $y = null, int $expected = null): void
    {
        $this->assertSame(1, $this->piece->checkMove($this->x, $this->y + 2, $this->board, 0));
        $this->assertSame(1, $this->piece->checkMove($this->x, $this->y + 1, $this->board, 0));

        $this->piece->incrementMovesCounter();
        $this->assertSame(0, $this->piece->checkMove($this->x, $this->y + 2, $this->board, 2));

        $this->assertSame(0, $this->piece->checkMove($this->x + 1, $this->y + 1, $this->board, 0));
        $this->assertSame(0, $this->piece->checkMove($this->x + 1, $this->y, $this->board, 0));
    }


    function testJsonSerialize(): void
    {
        $factory = new Factory();
        $json = [
            Piece::FIELD_TYPE => $factory->getLetter($this->piece),
            Piece::FIELD_MOVES_COUNTER => 0,
            Pawn::FIELD_EN_PASSANT => 0
        ];
        $this->assertSame(
            true,
            count(array_intersect($json, $this->piece->jsonSerialize())) === count($json)
        );
    }

    public function coordsProvider(): array
    {
        return [];
    }
}
