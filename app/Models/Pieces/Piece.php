<?php

namespace App\Models\Pieces;


use App\Models\Game;

abstract class Piece
{
    private bool $color;
    private int $x;
    private int $y;
    private int $movesCounter;
    private Game $game;


    /**
     * Creates a new schema of piece.
     *
     * @param bool $color 0 - black; 1 - white
     * @param int $x
     * @param int $y
     * @param Game $game
     */
    public function __construct(bool $color, int $x, int $y, Game $game)
    {
        $this->color = $color;
        $this->x = $x;
        $this->y = $y;
        $this->movesCounter = 0;
        $this->game = $game;
    }


    /**
     * Returns the piece color.
     *
     * @return bool
     */
    public function getColor(): bool
    {
        return $this->color;
    }


    /**
     * Returns the piece coords.
     *
     * @return array
     */
    public function getCoords(): array
    {
        return [$this->x, $this->y];
    }


    /**
     * Checks if the move complies with rules.
     *
     * @param int $x
     * @param int $y
     *
     * @return bool
     */
    public abstract function checkMove(int $x, int $y): bool;


    /**
     * Returns the number of cells to move forward based on piece color.
     *
     * @param int $cells
     *
     * @return int
     */
    public function forward(int $cells = 1): int
    {
        if ($this->getColor()) {
            return -abs($cells);
        }
        return abs($cells);
    }


    /**
     * Returns the piece moves counter.
     *
     * @return int
     */
    public function getMovesCounter(): int
    {
        return $this->movesCounter;
    }


    /**
     * Returns the game.
     *
     * @return Game
     */
    public function getGame(): Game
    {
        return $this->game;
    }
}
