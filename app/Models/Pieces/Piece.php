<?php

namespace App\Models\Pieces;


use App\Exceptions\Database\DatabaseInvalidPieceException;
use JsonSerializable;

abstract class Piece implements JsonSerializable
{
    private int $color;
    private int $x;
    private int $y;
    private int $movesCounter;

    public const FIELD_TYPE = 'type';
    public const FIELD_MOVES_COUNTER = 'movesCounter';


    /**
     * Creates a new schema of piece.
     *
     * @param int $color 0 - black; 1 - white
     * @param int $x
     * @param int $y
     * @param int $movesCounter
     */
    public function __construct(int $color, int $x, int $y, int $movesCounter)
    {
        $this->color = $color;
        $this->x = $x;
        $this->y = $y;
        $this->movesCounter = $movesCounter;
    }


    /**
     * Returns the piece color.
     *
     * @return int
     */
    public function getColor(): int
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
     * @param array $board
     * @param int $moveNumber
     *
     * @return int
     */
    public abstract function checkMove(int $x, int $y, array $board, int $moveNumber): int;


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
     * Increments the piece moves counter.
     */
    public function incrementMovesCounter(): void
    {
        ++$this->movesCounter;
    }


    /**
     * Sets new coords.
     *
     * @param int $x
     * @param int $y
     */
    public function setCoords(int $x, int $y): void
    {
        $this->x = $x;
        $this->y = $y;
        $this->incrementMovesCounter();
    }


    /**
     * Increments the piece moves counter.
     *
     * @throws DatabaseInvalidPieceException
     */
    public function jsonSerialize() {
        $factory = new Factory();
        return [
            self::FIELD_TYPE => $factory->getLetter($this),
            self::FIELD_MOVES_COUNTER => $this->getMovesCounter()
        ];
    }
}
