<?php

namespace App\Models\Pieces;


abstract class Piece
{
    private bool $color;
    private int $x;
    private int $y;


    /**
     * Creates a new schema of piece.
     *
     * @param bool $color 0 - black; 1 - white
     * @param int $x
     * @param int $y
     */
    public function __construct(bool $color, int $x, int $y)
    {
        $this->color = $color;
        $this->x = $x;
        $this->y = $y;
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
}
