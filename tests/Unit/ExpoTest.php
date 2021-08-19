<?php

namespace ExponentPhpSDK\Tests\Unit;

use ExponentPhpSDK\Expo;
use ExponentPhpSDK\Env;
use PHPUnit\Framework\TestCase;

class ExpoTest extends TestCase {

    /** @test */
    public function expo_instantiates()
    {
        $expo = Expo::normalSetup();

        $this->assertInstanceOf(Expo::class, $expo);

        return $expo;
    }

    /** @test */
    public function normal_setup_returns_a_file_driver()
    {
        $expo = Expo::normalSetup();

        $this->assertEquals('file', $expo->getDriver());
    }

    /** @test */
    public function custom_database_table_name_overrides_default()
    {
        $env = new Env();

        $this->assertNotEquals(
            $env->get('EXPO_TABLE'),
            'expo_tokens'
        );
    }
}
