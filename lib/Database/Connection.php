<?php

namespace ExponentPhpSDK\Database;

interface Connection {

    /**
     * Gets a database connection.
     *
     * @return self
     */
    public function connect();

    /**
     * Closes the database connection.
     *
     * @return void
     */
    public function close();
}
