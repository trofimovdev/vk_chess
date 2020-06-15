<?php

namespace App\Models\Pieces;


class Rook extends Piece
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

        if ($coords[0] === $x || $coords[1] === $y) {
            return true;
        }

        return false;
    }
}
