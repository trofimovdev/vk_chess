<?php

namespace Tests;

use App\Models\Pieces\Bishop;


class BishopTest extends PieceTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->piece = new Bishop(0, $this->x, $this->y, 0);
        $this->board[$this->y][$this->x] = $this->piece;
    }


    public function coordsProvider(): array
    {
        return [
            [$this->x + 2, $this->y + 2, 1],
            [$this->x + 2, $this->y - 2, 1],
            [$this->x - 2, $this->y + 2, 1],
            [$this->x - 2, $this->y - 2, 1],

            [$this->x, $this->y + 2, 0],
            [$this->x + 2, $this->y, 0]
        ];
    }
}
