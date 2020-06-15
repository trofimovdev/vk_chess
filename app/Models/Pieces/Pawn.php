<?php

namespace App\Models\Pieces;

use App\Models\Game;


class Pawn extends Piece
{
    private int $enPassant;

    /**
     * {@inheritDoc}
     */
    public function __construct(bool $color, int $x, int $y, Game $game)
    {
        parent::__construct($color, $x, $y, $game);
        $this->enPassant = 0;
    }


    /**
     * {@inheritDoc}
     */
    public function checkMove(int $x, int $y): bool
    {
        $coords = $this->getCoords();
        $game = $this->getGame();
        $cell = $game->getCell($x, $y);

        // en passant
        if (
            $this->getMovesCounter() === 0 &&
            $coords[0] === $x && $coords[1] + $this->forward(2) == $y &&
            is_null($game->getCell($coords[0], $coords[1] + $this->forward(1))) &&
            is_null($cell)
        ) {
            $this->enPassant = $moveNumber;
            return true;
        }

        // basic move
        if (
            $coords[0] === $x && $coords[1] + $this->forward(1) == $y &&
            is_null($cell)
        ) {
            return true;
        }

        // capturing
        if (
            ($coords[0] + 1 === $x || $coords[0] - 1 === $x) &&
            $coords[1] + $this->forward(1) == $y &&
            (!is_null($cell) ||
        ) {
            // capturing
            if ($cell instanceof Piece) {
                $cell->getEnPassant();
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
