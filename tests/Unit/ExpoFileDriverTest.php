<?php

namespace ExponentPhpSDK\Tests\Unit;

use ExponentPhpSDK\Env;
use ExponentPhpSDK\Expo;
use ExponentPhpSDK\Repositories\ExpoFileDriver;
use PHPUnit\Framework\TestCase;

class ExpoFileDriverTest extends TestCase {

    private $storagePath;

    protected function setUp(): void
    {
        $this->driver = new ExpoFileDriver();
        $this->storagePath = (new Env())->get('EXPO_STORAGE');
    }

    protected function tearDown(): void
    {
        $empty = json_encode(new \stdClass());
        file_put_contents($this->storagePath, $empty);
    }

    private function getStorageArray()
    {
        $file = file_get_contents($this->storagePath);

        return json_decode($file, true);
    }

    /** @test */
    public function expo_instantiates()
    {
        $expo = Expo::driver('file');

        $this->assertInstanceOf(Expo::class, $expo);

        return $expo;
    }

    /**
     * @depends expo_instantiates
     * @test
     */
    public function expo_returns_a_file_driver(Expo $expo)
    {
        $this->assertEquals('file', $expo->getDriver());
    }

    /**
     * @depends expo_instantiates
     * @test
     */
    public function expo_can_subscribe_to_a_channel(Expo $expo)
    {
        $channel = 'default';
        $token = 'ExponentPushToken[token]';
        $expo->subscribe($channel, $token);

        $storage = $this->getStorageArray();

        $this->assertSame([$token], $storage[$channel]);
    }

    /**
     * @depends expo_instantiates
     * @test
     */
    public function expo_can_unsubscribe_a_single_token_from_a_channel(Expo $expo)
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

    /**
     * @depends expo_instantiates
     * @test
     */
    public function expo_can_unsubscribe_all_tokens_from_a_channel(Expo $expo)
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

        $expo->unsubscribeAll($channel);
        $storage = $this->getStorageArray();

        // no tokens subscribed
        $this->assertSame([], $storage);
    }
}
