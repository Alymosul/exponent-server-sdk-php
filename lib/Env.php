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
        return $_ENV[$key];
    }

    public function has($key)
    {
        return (bool) $_ENV[$key];
    }
}
