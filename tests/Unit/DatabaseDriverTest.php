<?php

namespace ExponentPhpSDK\Tests\Unit;

use PHPUnit\Framework\TestCase;

class DatabaseDriverTest extends TestCase {

    protected function setUp(): void
    {
        //
    }

    /**
     * @skip
     */
    public function testDatabaseDriverIsInstantiated()
    {
        // $driver = Expo::driver('database');
        // $this->assertInstanceOf(ExpoDatabaseDriver::class, $driver);

        $this->markTestIncomplete(
          'This test has not been implemented yet. Need Sqlite database.'
        );

    }

    public function testRecipients()
    {
        $recipients = ['ExponentPushToken[aaaaaaaaaaaaaaaa]'];
        $this->assertCount(1, $recipients);

        return $recipients;
    }

    /**
     * @depends testRecipients
     */
    public function testRecipientsAreNotEmpty(array $recipients)
    {
        $this->assertNotEmpty($recipients);
    }

    protected function tearDown(): void
    {
        //
    }
}
