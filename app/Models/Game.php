<?php

namespace App\Models;

use App\Exceptions\Database\DatabaseGameNotCreatedException;
use App\Exceptions\Database\DatabaseInvalidPieceException;
use App\Exceptions\Game\GameInvalidCoords;
use App\Exceptions\Game\GameRulesException;
use App\Exceptions\Http\HttpNotFoundException;
use App\Exceptions\Http\HttpRequestException;
use App\Models\Pieces\Factory;
use App\Models\Pieces\Piece;


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

        $this->board = $this->parseBoard($game->board);
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
     *
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
            $response[count($response) - 1][] = $factory->getPiece($letter, $i % 8, intdiv($i, 8), $this);
        }
        return $response;
    }


    /**
     * Moves piece.
     *
     * @throws GameInvalidCoords
     * @throws HttpRequestException
     * @throws GameRulesException
     */
    public function move()
    {
        if (!isset($_REQUEST['from'])) {
            throw new HttpRequestException('No from passed.', 400);
        }
        if (!isset($_REQUEST['to'])) {
            throw new HttpRequestException('No to passed.', 400);
        }
        if ($_REQUEST['from'] === $_REQUEST['to']) {
            throw new HttpRequestException('From and to are same.', 400);
        }

        $from = $this->parseCoords($_REQUEST['from']);
        $to = $this->parseCoords($_REQUEST['to']);

        $fromCell = $this->getCell($from[0], $from[1]);
        if ($fromCell === null) {
            throw new HttpRequestException('From cell is empty.', 400);
        }
        if ($fromCell->getColor() !== $this->getTurn()) {
            throw new GameRulesException('Not your turn.', 403);
        }
        if (!$fromCell->checkMove($to[0], $to[1], $this->getMoveNumber())) {
            throw new GameRulesException('Bad move.', 400);
        }

        print_r([$from, $this->getCell($from[0], $from[1]), $to, $this->getCell($to[0], $to[1])]);
//        print_r($this->getCell(2, 2));
    }


    /**
     * Parses chess coords.
     *
     * @param string $coords
     *
     * @return array
     * @throws GameInvalidCoords
     */
    private function parseCoords(string $coords): array
    {
        $letter = strtoupper((string)$coords[0]);
        $num = (int)$coords[1] - 1;

        if (
            ord($letter) < 65 || ord($letter) > 72 ||
            $num < 0 || $num > 7
        ) {
            throw new GameInvalidCoords('Invalid coords', 500);
        }

        return [ord($letter) - 65, $num];
    }


    /**
     * Gets cell by coords.
     *
     * @param int $x
     * @param int $y
     *
     * @return Piece|null
     */
    public function getCell(int $x, int $y)
    {
        return $this->board[$x][$y];
    }


    /**
     * Gets cell by coords.
     *
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     *
     * @return bool
     */
    public function areEmptyCells(int $x1, int $y1, int $x2, int $y2): bool
    {

        return $this->board[$x][$y];
    }
}
