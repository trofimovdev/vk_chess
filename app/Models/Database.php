<?php

namespace App\Models;

use PDO;


class Database
{
    protected PDO $con;


    /**
     * Creates a new schema of database.
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

    public function query(string $sql, array $params = [])
    {
        $query = $this->con->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_CLASS);
    }
}
