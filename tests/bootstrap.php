<?php

use Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

define('TEST_DIR', __DIR__ );

$dotenv = Dotenv::createImmutable(__DIR__.'/..', ['.env.testing']);
$dotenv->load();
