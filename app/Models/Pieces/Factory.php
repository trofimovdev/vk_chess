<?php

namespace App\Models\Pieces;

use App\Exceptions\Database\DatabaseInvalidPieceException;

class Factory
{
    public function getPiece(string $letter, int $x, int $y)
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
        }
        throw new DatabaseInvalidPieceException('Invalid piece', 500);
    }
}
