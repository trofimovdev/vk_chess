<?php

namespace App\Models\Pieces;


class King extends Piece
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
        // if color is black
        if ($this->getColor() === 0) {

        } else {

        }
    }
}
