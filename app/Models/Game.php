<?php

namespace App\Models;

use App\Exceptions\Database\DatabaseGameNotCreatedException;
use App\Exceptions\Database\DatabaseInvalidPieceException;
use App\Exceptions\Game\GameInvalidCoords;
use App\Exceptions\Game\GameRulesException;
use App\Exceptions\Http\HttpNotFoundException;
use App\Exceptions\Http\HttpRequestException;
use App\Models\Pieces\Bishop;
use App\Models\Pieces\Factory;
use App\Models\Pieces\King;
use App\Models\Pieces\Knight;
use App\Models\Pieces\Pawn;
use App\Models\Pieces\Piece;
use App\Models\Pieces\Queen;
use App\Models\Pieces\Rook;


class Game
{
    private int $id;
    private array $board;
    private int $moveNumber;
    private int $status;
    private Database $db;

    private const FIELD_ID          = 'id';
    private const FIELD_MOVE_NUMBER = 'moveNumber';
    private const FIELD_TURN        = 'turn';
    private const FIELD_STATUS      = 'status';
    private const STATUS_RUNNING    = 0;
    private const STATUS_CHECK      = 1;
    private const STATUS_MATE       = 2;


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
        $game = $this->db->query('SELECT * FROM games WHERE id = ? LIMIT 1;', [$gameId]);
        if (!$game) {
            throw new HttpNotFoundException('Game not found', 404);
        }
        $game = $game[0];

        $this->id = $game->id;
        $this->board = $this->jsonDecode($game->board);
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
        $whitePawns = [];
        $blackPawns = [];
        $emptyCells = [];
        for ($i = 0; $i < 8; ++$i) {
            $whitePawns[] = new Pawn(1, $i, 6, 0, 0);
            $blackPawns[] = new Pawn(0, $i, 1, 0, 0);
            $emptyCells[] = null;
        }
        $initBoard = [
            [
                new Rook(0, 0, 0, 0),
                new Knight(0, 1, 0, 0),
                new Bishop(0, 2, 0, 0),
                new Queen(0, 3, 0, 0),
                new King(0, 4, 0, 0),
                new Bishop(0, 5, 0, 0),
                new Knight(0, 6, 0, 0),
                new Rook(0, 7, 0, 0)
            ],
            $blackPawns,
            $emptyCells,
            $emptyCells,
            $emptyCells,
            $emptyCells,
            $whitePawns,
            [
                new Rook(1, 0, 7, 0),
                new Knight(1, 1, 7, 0),
                new Bishop(1, 2, 7, 0),
                new Queen(1, 3, 7, 0),
                new King(1, 4, 7, 0),
                new Bishop(1, 5, 7, 0),
                new Knight(1, 6, 7, 0),
                new Rook(1, 7, 7, 0)
            ]
        ];
        $initBoard = [
            [
                new Rook(0, 0, 0, 0),
                null,
                null,
                null,
                new King(0, 4, 0, 0),
                null,
                null,
                new Rook(0, 7, 0, 0)
            ],
            $blackPawns,
            $emptyCells,
            $emptyCells,
            $emptyCells,
            $emptyCells,
            $whitePawns,
            [
                new Rook(1, 0, 7, 0),
                null,
                null,
                null,
                new King(1, 4, 7, 0),
                null,
                null,
                new Rook(1, 7, 7, 0)
            ]
        ];

        $game = $this->db->query('INSERT INTO games (board, move_number, status) VALUES (?, ?, ?);',
                                 [$this->jsonEncode($initBoard), 1, self::STATUS_RUNNING]);
        if (!$game) {
            throw new DatabaseGameNotCreatedException('Game not created', 500);
        }

        return [
            self::FIELD_ID => (int)$this->db->getCon()->lastInsertId(),
            self::FIELD_MOVE_NUMBER => 1,
            self::FIELD_TURN => 1
        ];
    }


    /**
     * Gets game status.
     *
     * @return array
     */
    public function status(): array
    {
        return [
            self::FIELD_MOVE_NUMBER => $this->getMoveNumber(),
            self::FIELD_TURN => $this->getTurn(),
            self::FIELD_STATUS => $this->getStatus(),
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
        return $this->getMoveNumber() % 2 !== 0;
    }


    /**
     * Parses JSON board to array.
     *
     * @param string $board
     *
     * @return array
     * @throws DatabaseInvalidPieceException
     */
    private function jsonDecode(string $board): array
    {
        $board = json_decode($board);
        $factory = new Factory();
        $response = [];
        for ($row = 0; $row < count($board); ++$row) {
            $response[] = [];
            for ($col = 0; $col < count($board[$col]); ++$col) {
                if ($board[$row][$col]->type === '.') {
                    $response[count($response) - 1][] = null;
                    continue;
                }

                $response[count($response) - 1][] =
                    $factory->getPiece(
                        $board[$row][$col]->type,
                        $col,
                        $row,
                        $board[$row][$col]->movesCounter,
                        isset($board[$row][$col]->enPassant) ? $board[$row][$col]->enPassant : null
                    );
            }
        }
        return $response;
    }


    /**
     * Moves piece.
     *
     * @throws GameInvalidCoords
     * @throws HttpRequestException
     * @throws GameRulesException
     * @throws HttpNotFoundException
     */
    public function move()
    {
        if (in_array($this->getStatus(), [self::STATUS_MATE])) {
            throw new HttpNotFoundException('Game over', 404);
        }

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

        $move = $fromCell->checkMove($to[0], $to[1], $this->getBoard(), $this->getMoveNumber());
        if (!$move) {
            throw new GameRulesException('Bad move.', 400);
        }
        if (!($fromCell instanceof Knight) && !$this->isReachable($from, $to)) {
            throw new GameRulesException('Cell is not reachable.', 500);
        }

        $board = $this->getBoard();
        switch ($move) {
            case Pawn::EN_PASSANT:
                $this->board[$to[1] - $fromCell->forward(1)][$to[0]] = null;
                $this->board[$from[1]][$from[0]]->enPassant = $this->getMoveNumber();
                break;

            case King::CASTLING_LEFT:
                if (!$this->isReachable($from, [$to[0] - 1, $to[1]])) {
                    throw new GameRulesException('Castling is impossible.', 500);
                }
                for ($i = 0; $i <= 1; ++$i) {
                    $this->board = $board;
                    if ($i) {
                        $this->board[$from[1]][$from[0] - $i] = $this->board[$from[1]][$from[0]];
                        $this->board[$from[1]][$from[0]] = null;
                    }
                    if ($this->isKingInCheck($this->getTurn())) {
                        throw new GameRulesException('Castling is impossible.', 500);
                    }
                }

                $this->board = $board;
                $this->board[$from[1]][3] = $this->board[$from[1]][0];
                $this->board[$from[1]][0] = null;
                $this->board[$from[1]][3]->setCoords(3, $from[1]);
                break;

            case King::CASTLING_RIGHT:
                for ($i = 0; $i <= 1; ++$i) {
                    $this->board = $board;
                    if ($i) {
                        $this->board[$from[1]][$from[0] + $i] = $this->board[$from[1]][$from[0]];
                        $this->board[$from[1]][$from[0]] = null;
                    }
                    if ($this->isKingInCheck($this->getTurn())) {
                        throw new GameRulesException('Castling is impossible.', 500);
                    }
                }
                $this->board = $board;
                $this->board[$from[1]][5] = $this->board[$from[1]][7];
                $this->board[$from[1]][7] = null;
                $this->board[$from[1]][5]->setCoords(5, $from[1]);
                break;
        }
        $this->board[$to[1]][$to[0]] = $this->board[$from[1]][$from[0]];
        $this->board[$from[1]][$from[0]] = null;

        if ($this->isKingInCheck($this->getTurn())) {
            throw new GameRulesException('Bad move: the King will be in check.', 400);
        }

        $this->incrementMoveNumber();
        $fromCell->setCoords($to[0], $to[1]);

        if ($this->isKingInCheck($this->getTurn())) {
            $this->status = self::STATUS_CHECK;
            if ($this->isKingInMate($this->getTurn())) {
                $this->status = self::STATUS_MATE;
            }
        }

        $this->db->query('UPDATE games SET board = ?, move_number = ?, status = ? WHERE id = ?;',
            [$this->jsonEncode($this->getBoard()), $this->getMoveNumber(), $this->getStatus(), $this->id]);

        return [
            self::FIELD_STATUS => $this->getStatus()
        ];
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

        return [ord($letter) - 65, 7 - $num];
    }


    /**
     * Gets cell by coords.
     *
     * @param int $x
     * @param int $y
     *
     * @return Piece|null
     */
    private function getCell(int $x, int $y)
    {
        return $this->board[$y][$x];
    }


    /**
     * Returns board.
     *
     * @return array
     */
    private function getBoard()
    {
        return $this->board;
    }


    /**
     * Returns JSON string of board.
     *
     * @param array $board
     *
     * @return string
     */
    private function jsonEncode(array $board): string
    {
        $response = [];
        for ($row = 0; $row < count($board); ++$row) {
            $response[] = [];
            for ($col = 0; $col < count($board[$col]); ++$col) {
                if (is_null($board[$row][$col])) {
                    $response[count($response) - 1][] = [
                        'type' => '.'
                    ];
                    continue;
                }
                $response[count($response) - 1][] = $board[$row][$col];
            }
        }
        return json_encode($response);
    }


    /**
     * Checks that cell is reachable.
     *
     * @param array $from
     * @param array $to
     *
     * @return bool
     */
    private function isReachable(array $from, array $to): bool
    {
        $cells = [];
        if ($from[0] === $to[0]) {
            for ($i = min($from[1], $to[1]); $i <= max($from[1], $to[1]); ++$i) {
                $cells[] = $this->getCell($from[0], $i);
            }
        } else if ($from[1] === $to[1]) {
            for ($i = min($from[0], $to[0]); $i <= max($from[0], $to[0]); ++$i) {
                $cells[] = $this->getCell($i, $from[1]);
            }
        } else {
            $x1 = min($from[0], $to[0]);
            $y1 = $x1 === $from[0] ? $from[1] : $to[1];
            $x2 = max($from[0], $to[0]);
            $y2 = $x2 === $from[0] ? $from[1] : $to[1];
            while (abs($x1 - $x2) >= 0 && abs($y1 - $y2) >= 0) {
                $cells[] = $this->getCell($x1, $y1);
                if ($x1 > $x2) {
                    --$x1;
                } else if ($x1 < $x2) {
                    ++$x1;
                } else if ($x1 === $x2) {
                    break;
                }
                if ($y1 > $y2) {
                    --$y1;
                } else if ($y1 < $y2) {
                    ++$y1;
                } else if ($y1 === $y2) {
                    break;
                }
            }
        }

        $pieces = count(array_filter($cells, function ($i) {
            return !is_null($i);
        }));

        if ($pieces === 1) {
            return true;
        }
        if ($pieces === 2) {
            $result = 0;
            if ($cells[0] instanceof Piece) {
                $result += $cells[0]->getColor() !== $this->getCell($from[0], $from[1])->getColor() ? 1 : 0;
            }
            if ($cells[count($cells) - 1] instanceof Piece) {
                $result +=
                    $cells[count($cells) - 1]->getColor() !== $this->getCell($from[0], $from[1])->getColor() ? 1 : 0;
            }
            if (
                $this->getCell($from[0], $from[1]) instanceof Pawn &&
                $from[0] === $to[0]
            ) {
                $result += 1;
            }
            return $result === 1;
        }
        return false;
    }


    private function incrementMoveNumber(): void
    {
        ++$this->moveNumber;
    }

    /**
     * Returns game status.
     *
     * @return int
     */
    private function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Returns king coords.
     *
     * @param int $color
     *
     * @return array|null
     */
    private function getKingCoords(int $color): ?array
    {
        $board = $this->getBoard();
        for ($row = 0; $row < count($board); ++$row) {
            for ($col = 0; $col < count($board[$col]); ++$col) {
                if (
                    $board[$row][$col] instanceof King &&
                    $board[$row][$col]->getColor() === $color
                ) {
                    return [$col, $row];
                }
            }
        }
        return null;
    }

    /**
     * Checks if King in check.
     *
     * @param int $color
     *
     * @return bool
     */
    private function isKingInCheck(int $color): bool
    {
        $board = array_merge(...$this->getBoard());
        $coords = $this->getKingCoords($color);
        foreach ($board as $piece) {
            if (!($piece instanceof Piece)) {
                continue;
            }
            if ($piece->getColor() === $color) {
                continue;
            }
            $move = $piece->checkMove($coords[0], $coords[1], $this->getBoard(), $this->getMoveNumber());
            $pieceCoords = $piece->getCoords();
            if ($move && ($piece instanceof Knight || $this->isReachable($pieceCoords, $coords))) {
                return true;
            };
        }
        return false;
    }

    /**
     * Checks if King in mate.
     *
     * @param int $color
     *
     * @return bool
     */
    private function isKingInMate(int $color): bool
    {
        $board = $this->getBoard();
        $flattenBoard = array_merge(...$this->getBoard());
        $coords = $this->getKingCoords($color);

        // король может уйти
        for ($row = 0; $row <= 2; ++$row) {
            for ($col = 0; $col <= 2; ++$col) {
                $x = $coords[0] + (1 - $row);
                $y = $coords[1] + (1 - $col);
                if (
                    ($x > 7 || $x < 0) ||
                    ($y > 7 || $y < 0) ||
                    ($y === $coords[1] && $x === $coords[0]) ||
                    !$this->isReachable($coords, [$x, $y])
                ) {
                    continue;
                }

                $this->board[$y][$x] = $this->board[$coords[1]][$coords[0]];
                $this->board[$coords[1]][$coords[0]] = null;
                if (!$this->isKingInCheck($color)) {
                    $this->board = $board;
                    return false;
                }
            }
        }
        $this->board = $board;

        // можно закрыть или съесть, перебираем все ходы
        foreach ($flattenBoard as $piece) {
            if (!($piece instanceof Piece) || $piece instanceof King) {
                continue;
            }
            if ($piece->getColor() !== $color) {
                continue;
            }

            $pieceCoords = $piece->getCoords();
            for ($y = 0; $y < 8; ++$y) {
                for ($x = 0; $x < 8; ++$x) {
                    $this->board = $board;
                    $move = $piece->checkMove($x, $y, $board, $this->getMoveNumber());
                    if (!$move || !$this->isReachable($pieceCoords, [$x, $y])) {
                        continue;
                    }
                    $this->board[$y][$x] = $this->board[$pieceCoords[1]][$pieceCoords[0]];
                    $this->board[$pieceCoords[1]][$pieceCoords[0]] = null;
                    if (!$this->isKingInCheck($color)) {
                        $this->board = $board;
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
