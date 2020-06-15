<?php

namespace App\Models\Pieces;


class Queen extends Piece
{
    /**
     * {@inheritDoc}
     */
    public function __construct(bool $color, int $x, int $y, int $movesCounter)
    {
        parent::__construct($color, $x, $y, $movesCounter);
    }


    /**
     * {@inheritDoc}
     */
    public function checkMove(int $x, int $y, array $board, int $moveNumber): int
    {
        $coords = $this->getCoords();

        if (
            abs($coords[0] - $x) === abs($coords[1] - $y) ||
            abs($coords[0] - $x) === 0 ||
            abs($coords[1] - $y) === 0
        ) {
            return true;
        }

        return false;
    }
}
