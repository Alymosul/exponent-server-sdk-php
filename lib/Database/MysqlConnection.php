<?php

namespace ExponentPhpSDK\Database;

use Doctrine\DBAL\DriverManager;
use ExponentPhpSDK\Database\Connection;

class MysqlConnection implements Connection {

    /**
     * The database connection.
     *
     * @var \Doctrine\DBAL\Driver\Connection|null
     */
    public $conn = null;

    /**
     * Establishes a database connection.
     *
     * @return self
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
        $this->conn = null;
    }

    /**
     * Get the database credentials.
     *
     * @return array
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

    /**
     * Returns a database query builder.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder;
     */
    public function getQuery()
    {
        return $this->conn->createQueryBuilder();
    }
}
