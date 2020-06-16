<?php

namespace App\Models\Pieces;


use App\Exceptions\Database\DatabaseInvalidPieceException;

class Pawn extends Piece
{
    private int $enPassant;
    public const EN_PASSANT = 3;


    /**
     * {@inheritDoc}
     * @param int $enPassant
     */
    public function __construct(int $color, int $x, int $y, int $movesCounter, int $enPassant)
    {
        parent::__construct($color, $x, $y, $movesCounter);
        $this->enPassant = $enPassant;
    }


    /**
     * {@inheritDoc}
     */
    public function checkMove(int $x, int $y, array $board, int $moveNumber): int
    {
        $coords = $this->getCoords();

        // en passant move
        if (
            $this->getMovesCounter() === 0 &&
            $coords[0] === $x && $coords[1] + $this->forward(2) === $y
        ) {
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
            abs($coords[0] - $x) === 1 &&
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

            if (
                !is_null($board[$y][$x]) && $board[$y][$x]->getColor() !== $this->getColor()
            ) {
                return true;
            }
        }
        return false;
    }


    public function getEnPassant(): int
    {
        return $this->enPassant;
    }


    /**
     * Increments the piece moves counter.
     *
     * @throws DatabaseInvalidPieceException
     */
    public function jsonSerialize() {
        $factory = new Factory();
        return [
            'type' => $factory->getLetter($this),
            'movesCounter' => $this->getMovesCounter(),
            'enPassant' => $this->getEnPassant()
        ];
    }
}
