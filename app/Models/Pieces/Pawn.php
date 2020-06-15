<?php

namespace App\Models\Pieces;


class Pawn extends Piece
{
    private int $enPassant;
    public const EN_PASSANT = 3;


    /**
     * {@inheritDoc}
     */
    public function __construct(int $color, int $x, int $y)
    {
        parent::__construct($color, $x, $y);
        $this->enPassant = 0;
    }


    /**
     * {@inheritDoc}
     */
    public function checkMove(int $x, int $y, array $board, int $moveNumber): int
    {
        $coords = $this->getCoords();
        print_r($coords);
        print_r([$x, $y]);

        // en passant move
        if (
            $this->getMovesCounter() === 0 &&
            $coords[0] === $x && $coords[1] + $this->forward(2) === $y
        ) {
            $this->enPassant = $moveNumber;
            return true;
        }

        // basic move
        if (
            $coords[0] === $x && $coords[1] + $this->forward(1) === $y
        ) {
            return true;
        }

        // capturing
        if (
            ($coords[0] + 1 === $x || $coords[0] - 1 === $x) &&
            $coords[1] + $this->forward(1) === $y
        ) {
            // en passant
            $enPassantCell = $board[$y - $this->forward(1)][$x];
            if (
                $enPassantCell instanceof Pawn &&
                $enPassantCell->getEnPassant() === $moveNumber - 1
            ) {
                return self::EN_PASSANT;
            }
            return true;
        }
        return false;
    }


    public function getEnPassant(): int
    {
        return $this->enPassant;
    }
}
