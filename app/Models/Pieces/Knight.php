<?php

namespace App\Models\Pieces;


class Knight extends Piece
{
    /**
     * {@inheritDoc}
     */
    public function __construct(bool $color, int $x, int $y)
    {
        parent::__construct($color, $x, $y);
    }


    /**
     * {@inheritDoc}
     */
    public function checkMove(int $x, int $y, array $board, int $moveNumber): int
    {
        $coords = $this->getCoords();

        if (
            abs($coords[0] - $x) === 1 && abs($coords[1] - $y) === 2 ||
            abs($coords[0] - $x) === 2 && abs($coords[1] - $y) === 1
        ) {
            return true;
        }

        return false;
    }
}
