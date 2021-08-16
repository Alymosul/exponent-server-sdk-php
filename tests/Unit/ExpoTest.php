<?php

namespace ExponentPhpSDK\Tests\Unit;

use ExponentPhpSDK\Expo;
use ExponentPhpSDK\Env;
use PHPUnit\Framework\TestCase;

class ExpoTest extends TestCase {

    public function testExpoInstantiates()
    {
        $expo = Expo::normalSetup();

        $this->assertInstanceOf(Expo::class, $expo);

        return $expo;
    }

    public function testNormalSetupReturnsFileDriver()
    {
        $expo = Expo::normalSetup();

        $this->assertEquals('file', $expo->getDriver());
    }

    public function testCustomDatabaseTableNameOverridesDefault()
    {
        $env = new Env();

        $this->assertNotEquals(
            $env->get('EXPO_TABLE'),
            'expo_tokens'
        );
    }
}
