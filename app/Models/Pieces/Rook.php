<?php

namespace App\Models\Pieces;


class Rook extends Piece
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

        if ($coords[0] === $x || $coords[1] === $y) {
            return true;
        }

        return false;
    }
}
