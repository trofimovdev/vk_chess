<?php

namespace App\Models\Pieces;

use App\Exceptions\Database\DatabaseInvalidPieceException;


class Factory
{
    /**
     * Returns {@link Piece} object by letter.
     *
     * @param string $letter
     * @param int $x
     * @param int $y
     *
     * @return Piece
     * @throws DatabaseInvalidPieceException
     */
    public function getPiece(string $letter, int $x, int $y): Piece
    {
        $color = strtoupper($letter) === $letter;

        switch (strtoupper($letter)) {
            case 'K':
                return new King($color, $x, $y);
                break;

            case 'B':
                return new Bishop($color, $x, $y);
                break;

            case 'H':
                return new Knight($color, $x, $y);
                break;

            case 'P':
                return new Pawn($color, $x, $y);
                break;

            case 'Q':
                return new Queen($color, $x, $y);
                break;

            case 'R':
                return new Rook($color, $x, $y);
                break;

            default:
                throw new DatabaseInvalidPieceException('Invalid piece', 500);
        }
    }


    /**
     * Returns letter by {@link Piece} object.
     *
     * @param Piece $piece
     *
     * @return string
     * @throws DatabaseInvalidPieceException
     */
    public function getLetter(Piece $piece): string
    {
        $pieceClass = get_class($piece);
        switch ($pieceClass) {
            case King::class:
                $letter = 'K';
                break;

            case Bishop::class:
                $letter = 'B';
                break;

            case Knight::class:
                $letter = 'H';
                break;

            case Pawn::class:
                $letter = 'P';
                break;

            case Queen::class:
                $letter = 'Q';
                break;

            case Rook::class:
                $letter = 'R';
                break;

            default:
                throw new DatabaseInvalidPieceException('Invalid piece', 500);
        }
        return $piece->getColor() ? strtoupper($letter) : strtolower($letter);
    }


}
