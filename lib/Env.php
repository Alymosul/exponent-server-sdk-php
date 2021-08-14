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
        // Allows for a custom table name to store subscriptions.
        if ($key === 'EXPO_TABLE') {
            return $_ENV[$key] ?? 'expo_tokens';
        }

        return $_ENV[$key];
    }

    public function has($key)
    {
        return (bool) $_ENV[$key];
    }
}
