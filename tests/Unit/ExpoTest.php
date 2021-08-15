<?php

namespace ExponentPhpSDK\Tests\Unit;

use ExponentPhpSDK\Expo;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

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
}
