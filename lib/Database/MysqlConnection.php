<?php

namespace ExponentPhpSDK\Database;

use Doctrine\DBAL\DriverManager;
use ExponentPhpSDK\Database\Connection;
use ExponentPhpSDK\Env;

class MysqlConnection implements Connection {

    /**
     * Environment variables.
     *
     * @var Env
     */
    public $env;

    /**
     * The database connection.
     *
     * @var \Doctrine\DBAL\Driver\Connection|null
     */
    public $conn = null;

    public function __construct()
    {
        $this->env = new Env();
    }

    /**
     * Establishes a database connection.
     *
     * @return MysqlConnection
     */
    public function connect()
    {
        if (! $this->conn) {
            $this->conn = DriverManager::getConnection(
                $this->getCredentials()
            );
        }

        return $this;
    }

    /**
     * Closes the database connection.
     *
     * @return void
     */
    public function close()
    {
        $this->conn->close();
        $this->conn = null;
    }

    /**
     * Returns a database query builder.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder;
     */
    public function getQuery()
    {
        return $this->conn->createQueryBuilder();
    }

    /**
     * Gets the database credentials from environment.
     *
     * @return array
     * @throws \Exception
     */
    private function getCredentials()
    {
        return [
            'dbname' => $this->env->get('DB_DATABASE'),
            'user' => $this->env->get('DB_USERNAME'),
            'password' => $this->env->get('DB_PASSWORD'),
            'host' => $this->env->get('DB_HOST'),
            'port' => $this->env->get('DB_PORT'),
            'driver' => 'pdo_mysql',
        ];
    }
}
