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
    public function checkMove(int $x, int $y): bool
    {
        // if color is black
        if ($this->getColor() === 0) {

        } else {

        }
    }
}
