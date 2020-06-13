<?php

namespace App\Models\Pieces;


class Pawn extends Piece
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
    public function checkMove(int $x, int $y)
    {
        // if color is black
        if ($this->color === 0) {

        } else {

        }
    }
}
