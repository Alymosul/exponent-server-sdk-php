<?php

namespace ExponentPhpSDK\Tests\Unit;

use ExponentPhpSDK\Expo;
use PHPUnit\Framework\TestCase;

class ExpoTest extends TestCase {

    public function testTheDefaultDriverIsFile()
    {
        $expo = Expo::driver();

        $this->assertEquals('file', $expo->getDriver());
    }

}
