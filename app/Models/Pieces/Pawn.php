<?php

namespace App\Models\Pieces;

use App\Exceptions\Database\DatabaseInvalidPieceException;


class Pawn extends Piece
{
    private int $enPassant;
    public const EN_PASSANT = 3;
    public const TRANSFORM  = 6;
    public const FIELD_EN_PASSANT = 'enPassant';


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
        $result = 0;

        // en passant move
        if (
            $this->getMovesCounter() === 0 &&
            $coords[0] === $x && $coords[1] + $this->forward(2) === $y
        ) {
            $result = 1;
        }

        // basic move
        if (
            $coords[0] === $x && $coords[1] + $this->forward(1) === $y
        ) {
            $result = 1;
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
                $result = 1;
            }
        }

        if (
            $result &&
            (
                $this->getColor() && $y === 0 ||
                !$this->getColor() && $y === 7
            )
        ) {
            return self::TRANSFORM;
        }

        return $result;
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
            Piece::FIELD_TYPE => $factory->getLetter($this),
            Piece::FIELD_MOVES_COUNTER => $this->getMovesCounter(),
            self::FIELD_EN_PASSANT => $this->getEnPassant()
        ];
    }
}
