<?php

namespace ExponentPhpSDK\Tests\Unit;

use ExponentPhpSDK\Database\MysqlConnection;
use ExponentPhpSDK\Env;
use ExponentPhpSDK\Expo;
use ExponentPhpSDK\Repositories\ExpoMysqlDriver;
use PHPUnit\Framework\TestCase;

class ExpoMysqlDriverTest extends TestCase {

    private $env;
    private $conn;
    private $driver;

    protected function setUp(): void
    {
        $this->env = new Env();
        $this->conn = (new MysqlConnection())->connect();
        $this->driver = new ExpoMysqlDriver($this->conn);
        $this->table = $this->env->get('EXPO_TABLE');
    }

    protected function tearDown(): void
    {
        // Delete all existing channels
        $this->conn->getQuery()
            ->delete($this->table)
            ->executeQuery();

        $this->conn->close();
        $this->conn = null;
    }

    public function testExpoInstantiates()
    {
        $expo = Expo::driver('mysql');

        $this->assertInstanceOf(Expo::class, $expo);

        return $expo;
    }

    /**
     * @depends testExpoInstantiates
     */
    public function testExpoReturnsMysqlDriver(Expo $expo)
    {
        $this->assertEquals('mysql', $expo->getDriver());
    }

    /**
     * @depends testExpoInstantiates
     */
    public function testExpoCanSubscribeToAChannel(Expo $expo)
    {
        $channel = 'default';
        $token = 'ExponentPushToken[token]';
        $expo->subscribe($channel, $token);

        $result = $this->conn->getQuery()
            ->select('recipients')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();

        $this->assertSame([$token], json_decode($result));
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

        $subscriptions = $this->conn->getQuery()
            ->select('recipients')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();

        // two tokens subscribed
        $this->assertSame(
            [$token1, $token2],
            json_decode($subscriptions)
        );

        $expo->unsubscribe($channel, $token1);

        $subscriptions = $this->conn->getQuery()
            ->select('recipients')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();

        // one token subscribed
        $this->assertSame(
            [$token2],
            json_decode($subscriptions)
        );
    }

    /**
     * @depends testExpoInstantiates
     */
    public function testMysqlDriverCanForgetAToken(Expo $expo)
    {
        $channel = 'default';
        $token1 = 'ExponentPushToken[token-1]';
        $token2 = 'ExponentPushToken[token-2]';
        $expo->subscribe($channel, $token1);
        $expo->subscribe($channel, $token2);

        $subscriptions = $this->conn->getQuery()
            ->select('recipients')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();

        // two tokens subscribed
        $this->assertSame(
            [$token1, $token2],
            json_decode($subscriptions)
        );

        $this->driver->forget($channel, $token1);

        $subscriptions = $this->conn->getQuery()
            ->select('recipients')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();

        // one token subscribed
        $this->assertSame(
            [$token2],
            json_decode($subscriptions)
        );
    }

    public function testMysqlDriverCanStoreTokens()
    {
        $channel = 'default';
        $this->driver->store($channel, 'ExponentPushToken[token]');

        $result = (bool) $this->conn->getQuery()
            ->select('channel')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();

        $this->assertTrue($result);
    }

    public function testMysqlDriverCanRetrieveTokens()
    {
        $channel = 'default';
        $token = 'ExponentPushToken[token]';
        $this->driver->store($channel, $token);

        $tokens = $this->driver->retrieve($channel);

        $this->assertSame([$token], $tokens);
    }
}
