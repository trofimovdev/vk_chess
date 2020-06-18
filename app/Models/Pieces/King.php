<?php

namespace App\Models\Pieces;


class King extends Piece
{
    public const CASTLING_LEFT  = 4;
    public const CASTLING_RIGHT = 5;


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
            abs($coords[0] - $x) <= 1 &&
            abs($coords[1] - $y) <= 1
        ) {
            return true;
        }

        if (
            abs($coords[0] - $x) === 2 && $coords[1] === $y &&
            $this->getMovesCounter() === 0
        ) {
            if ($x > $coords[0] && $board[$coords[1]][7] instanceof Rook) {
                return $board[$coords[1]][7]->getMovesCounter() === 0 ? self::CASTLING_RIGHT : false;
            }
            if ($x < $coords[0] && $board[$coords[1]][0] instanceof Rook) {
                return $board[$coords[1]][0]->getMovesCounter() === 0 ? self::CASTLING_LEFT : false;
            }
        }

        return false;
    }
}
