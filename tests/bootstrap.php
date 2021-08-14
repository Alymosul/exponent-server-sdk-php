<?php

use Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

// Load .env.testing at package root into environment
$dotenv = Dotenv::createImmutable(__DIR__.'/..', ['.env.testing']);
$dotenv->load();
