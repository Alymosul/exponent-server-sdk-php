<?php

namespace ExponentPhpSDK;

use Dotenv\Dotenv;

class Env {

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(getcwd());
        $dotenv->safeLoad();
    }

    /**
     * Retrieves an environment value.
     *
     * @return string
     * @throws \Exception
     */
    public function get($key)
    {
        // Allows for a custom table name for databse drivers.
        if ($key === 'EXPO_TABLE') {
            return $this->getSafe($key) ?? 'expo_tokens';
        }

        return $_ENV[$key];
    }

    /**
     * Retrieves an environment value if it exists, null otherwise.
     *
     * @return string|null
     */
    public function getSafe($key)
    {
        try {
            return $_ENV[$key];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
