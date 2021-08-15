<?php

namespace ExponentPhpSDK;

use Dotenv\Dotenv;

class Env {

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(getcwd());
        $dotenv->safeLoad();
    }

    public function get($key)
    {
        // Allows for a custom table name for databse drivers.
        if ($key === 'EXPO_TABLE') {
            return $_ENV[$key] ?? 'expo_tokens';
        }

        return $_ENV[$key];
    }

    public function getSafe($key)
    {
        try {
            return $this->get($key);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function has($key)
    {
        return (bool) $_ENV[$key];
    }
}
