<?php

namespace App\Models;


class Game
{
    private array $board;
    private int $moveNumber;
    private Database $db;


    public function __construct()
    {
        $this->db = new Database($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
    }

    public function getStatus()
    {
//        return [$_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']];
        return $this->db->query('SELECT * FROM game;');
    }
}
