<?php

namespace ExponentPhpSDK\Tests\Unit;

use ExponentPhpSDK\Expo;
use ExponentPhpSDK\Repositories\ExpoFileDriver;
use PHPUnit\Framework\TestCase;

class ExpoFileDriverTest extends TestCase {

    private $storagePath;

    protected function setUp(): void
    {
        $this->setUpTempStorage();
        $this->driver = new ExpoFileDriver();
    }

    public function setUpTempStorage()
    {
        $this->tempDir = TEST_DIR . DIRECTORY_SEPARATOR . '.tmp';

        if (! is_dir($this->tempDir)) {
            mkdir($this->tempDir);
        }

        $this->storagePath = $this->tempDir . DIRECTORY_SEPARATOR . 'tokens.json';
    }

    protected function tearDown(): void
    {
        @unlink($this->storagePath);
        @rmdir($this->tempDir);
    }

    private function getStorageArray()
    {
        $file = file_get_contents($this->storagePath);

        return json_decode($file, true);
    }

    public function testExpoInstantiates()
    {
        $expo = Expo::driver('file');

        $this->assertInstanceOf(Expo::class, $expo);

        return $expo;
    }

    /**
     * @depends testExpoInstantiates
     */
    public function testExpoReturnsFileDriver(Expo $expo)
    {
        $this->assertEquals('file', $expo->getDriver());
    }

    /**
     * @depends testExpoInstantiates
     */
    public function testExpoCanSubscribeToAChannel(Expo $expo)
    {
        $channel = 'default';
        $token = 'ExponentPushToken[token]';
        $expo->subscribe($channel, $token);

        $storage = $this->getStorageArray();

        $this->assertSame([$token], $storage[$channel]);
    }

    /**
     * @depends testExpoInstantiates
     */
    public function testExpoCanUnsubscribeFromAChannel(Expo $expo)
    {
        $channel = 'default';
        $token1 = 'ExponentPushToken[token-1]';
        $token2 = 'ExponentPushToken[token-2]';
        $expo->subscribe($channel, $token1);
        $expo->subscribe($channel, $token2);

        $storage = $this->getStorageArray();

        // two tokens subscribed
        $this->assertSame(
            [$token1, $token2],
            $storage[$channel]
        );

        $expo->unsubscribe($channel, $token1);

        $storage = $this->getStorageArray();

        // one token subscribed
        $this->assertSame(
            [$token2],
            $storage[$channel]
        );
    }
}
