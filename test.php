<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $instance = \ExponentPhpSDK\Expo::normalSetup();
    echo 'Succeeded! We have created an Expo instance successfully';
} catch (Exception $e) {
    echo 'Test Failed';
}
