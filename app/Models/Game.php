<?php

namespace App\Models;

use App\Exceptions\Database\DatabaseGameNotCreatedException;
use App\Exceptions\Http\HttpNotFoundException;


class Game
{
    private $board;
    private int $moveNumber;
    private int $status;
    private Database $db;
    public const INIT_BOARD = 'R0H0B0Q0K0B0H0R0' .
                              'P0P0P0P0P0P0P0P0' .
                              '........' .
                              '........' .
                              '........' .
                              '........' .
                              'P1P1P1P1P1P1P1P1' .
                              'R1H1B1Q1K1B1H1R1';

    private const FIELD_ID = 'id';
    private const FIELD_MOVE_NUMBER = 'moveNumber';
    private const FIELD_TURN = 'turn';


    /**
     * Game constructor.
     *
     * @param int $gameId
     *
     * @throws HttpNotFoundException
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

        $this->board = $game->board;
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
            self::FIELD_TURN => $this->getTurn()
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
}
