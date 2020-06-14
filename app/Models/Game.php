<?php

namespace App\Models;

use App\Exceptions\Database\DatabaseGameNotCreatedException;
use App\Exceptions\Database\DatabaseInvalidPieceException;
use App\Exceptions\Http\HttpNotFoundException;
use App\Models\Pieces\Factory;


class Game
{
    private array $board;
    private int $moveNumber;
    private int $status;
    private Database $db;
    public const INIT_BOARD = 'rhbqkbhr' .
                              'pppppppp' .
                              '........' .
                              '........' .
                              '........' .
                              '........' .
                              'PPPPPPPP' .
                              'RHBQKBHR';

    private const FIELD_ID          = 'id';
    private const FIELD_MOVE_NUMBER = 'moveNumber';
    private const FIELD_TURN        = 'turn';


    /**
     * Game constructor.
     *
     * @param int $gameId
     *
     * @throws HttpNotFoundException
     * @throws DatabaseInvalidPieceException
     */
    public function __construct(int $gameId)
    {
        $this->db = new Database($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        if (!$gameId) {
            return;
        }
        $game = $this->db->query('SELECT * FROM games WHERE id = ?;', [$gameId]);
        if (!$game) {
            throw new HttpNotFoundException('Game not found', 404);
        }
        $game = $game[0];

        $this->board = self::parseBoard($game->board);
        print_r($this->board);
        $this->moveNumber = $game->move_number;
        $this->status = $game->status;
    }


    /**
     * Creates a new game.
     *
     * @return array
     * @throws DatabaseGameNotCreatedException
     */
    public function create(): array
    {
        $game = $this->db->query('INSERT INTO games (board, move_number, status) VALUES (?, ?, ?);',
                                 [self::INIT_BOARD, 0, 0]);
        if (!$game) {
            throw new DatabaseGameNotCreatedException('Game not created', 500);
        }

        return [
            self::FIELD_ID => $this->db->getCon()->lastInsertId(),
            self::FIELD_MOVE_NUMBER => 0,
            self::FIELD_TURN => 1
        ];
    }


    /**
     * Gets game status.
     *
     * @return array
     */
    public function getStatus(): array
    {
        return [
            self::FIELD_MOVE_NUMBER => $this->getMoveNumber(),
            self::FIELD_TURN => $this->getTurn(),
        ];
    }


    /**
     * Gets move number.
     *
     * @return int
     */
    private function getMoveNumber(): int
    {
        return $this->moveNumber;
    }


    /**
     * Gets turn.
     *
     * @return int
     */
    private function getTurn(): int
    {
        return $this->getMoveNumber() % 2 === 0;
    }


    /**
     * Parses string board to array.
     *
     * @param string $board
     * @return array
     * @throws DatabaseInvalidPieceException
     */
    private function parseBoard(string $board): array
    {
        $response = [];
        $factory = new Factory();
        $length = strlen($board);
        for ($i = 0; $i < $length; ++$i) {
            $letter = $board[$i];
            if (count($response) <= intdiv($i, 8)) {
                $response[] = [];
            }

            if ($letter === '.') {
                $response[count($response) - 1][] = null;
                continue;
            }
            $response[count($response) - 1][] = $factory->getPiece($letter, $i % 8, intdiv($i, 8));
        }
        return $response;
    }
}
