<?php

namespace Tests;

use App\Exceptions\Database\DatabaseInvalidPieceException;
use App\Models\Pieces\Factory;
use App\Models\Pieces\Piece;
use App\Models\Pieces\Bishop;
use App\Models\Pieces\King;
use App\Models\Pieces\Knight;
use App\Models\Pieces\Pawn;
use App\Models\Pieces\Queen;
use App\Models\Pieces\Rook;
use PHPUnit\Framework\TestCase;


class FactoryTest extends TestCase
{
    protected ?Factory $factory;


    protected function setUp(): void
    {
        $this->factory = new Factory();
    }


    protected function tearDown(): void
    {
        $this->factory = null;
    }


    /**
     * @dataProvider piecesProvider
     * @param Piece $piece
     * @param string $expected
     * @throws DatabaseInvalidPieceException
     */
    public function testGetLetter(Piece $piece, string $expected): void
    {
        $this->assertSame($expected, $this->factory->getLetter($piece));
    }


    public function piecesProvider(): array
    {
        return [
            [new Bishop(0, 0, 0, 0), 'b'],
            [new Bishop(1, 0, 0, 0), 'B'],
            [new King(0, 0, 0, 0), 'k'],
            [new King(1, 0, 0, 0), 'K'],
            [new Knight(0, 0, 0, 0), 'h'],
            [new Knight(1, 0, 0, 0), 'H'],
            [new Pawn(0, 0, 0, 0, 0), 'p'],
            [new Pawn(1, 0, 0, 0, 0), 'P'],
            [new Queen(0, 0, 0, 0), 'q'],
            [new Queen(1, 0, 0, 0), 'Q'],
            [new Rook(0, 0, 0, 0), 'r'],
            [new Rook(1, 0, 0, 0), 'R'],
        ];
    }


    /**
     * @dataProvider lettersProvider
     * @param string $letter
     * @param Piece $expected
     * @throws DatabaseInvalidPieceException
     */
    public function testGetPiece(string $letter, Piece $expected): void
    {
        $this->assertSame(
            true,
            $this->factory->getPiece($letter, 0, 0, 0, 0) instanceof $expected
        );
    }


    public function lettersProvider(): array
    {
        return [
            ['b', new Bishop(0, 0, 0, 0)],
            ['B', new Bishop(0, 0, 0, 0)],
            ['k', new King(0, 0, 0, 0)],
            ['K', new King(0, 0, 0, 0)],
            ['h', new Knight(0, 0, 0, 0)],
            ['H', new Knight(0, 0, 0, 0)],
            ['p', new Pawn(0, 0, 0, 0, 0)],
            ['P', new Pawn(0, 0, 0, 0, 0)],
            ['q', new Queen(0, 0, 0, 0)],
            ['Q', new Queen(0, 0, 0, 0)],
            ['r', new Rook(0, 0, 0, 0)],
            ['R', new Rook(0, 0, 0, 0)]
        ];
    }
}
