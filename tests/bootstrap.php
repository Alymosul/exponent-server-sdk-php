<?php

require __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;

define('TEST_DIR', __DIR__ );

$dotenv = Dotenv::createImmutable(__DIR__.'/..', ['.env.testing']);
$dotenv->load();
