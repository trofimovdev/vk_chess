<?php

namespace App\Models;

use PDO;


class Database
{
    private PDO $con;


    /**
     * Database constructor.
     *
     * @param string $host
     * @param string $dbname
     * @param string $user
     * @param string $password
     */
    public function __construct(string $host, string $dbname, string $user, string $password)
    {
        $this->con = new PDO('pgsql:host=' . $host . ' dbname=' . $dbname, $user, $password);
    }


    /**
     * Prepares and executes an SQL statement.
     *
     * @param string $sql
     * @param array $params
     *
     * @return array
     */
    public function query(string $sql, array $params = []): array
    {
        $query = $this->con->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_CLASS);
    }


    /**
     * Returns connection.
     *
     * @return PDO
     */
    public function getCon(): PDO
    {
        return $this->con;
    }
}
