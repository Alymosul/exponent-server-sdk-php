<?php

namespace ExponentPhpSDK\Tests\Unit;

use ExponentPhpSDK\Database\MysqlConnection;
use ExponentPhpSDK\Env;
use ExponentPhpSDK\Expo;
use ExponentPhpSDK\Repositories\ExpoMysqlDriver;
use PHPUnit\Framework\TestCase;

class MysqlDriverTest extends TestCase {

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

    public function testExpoInstantiates()
    {
        $expo = Expo::driver('mysql');

        $this->assertInstanceOf(Expo::class, $expo);

        return $expo;
    }

    /**
     * @depends testExpoInstantiates
     */
    public function testExpoReturnsAMysqlDriver(Expo $expo)
    {
        $this->assertEquals('mysql', $expo->getDriver());
    }

    /**
     * @depends testExpoInstantiates
     */
    public function testYouCanSubscribeToAChannel(Expo $expo)
    {
        $channel = 'events';
        $token = 'ExponentPushToken[some-obscure-token]';
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
    public function testYouCanUnsubscribeFromAChannel(Expo $expo)
    {
        $channel = 'events';
        $token1 = 'ExponentPushToken[some-obscure-token-1]';
        $token2 = 'ExponentPushToken[some-obscure-token-2]';
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

    public function testTheMysqlDriverCanStoreTokens()
    {
        $channel = 'default';
        $this->driver->store($channel, 'ExponentPushToken[some-obscure-token]');

        $result = (bool) $this->conn->getQuery()
            ->select('channel')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();

        $this->assertTrue($result);
    }

    public function testTheMysqlDriverCanRetrieveTokens()
    {
        $channel = 'default';
        $token = 'ExponentPushToken[some-obscure-token]';
        $this->driver->store($channel, $token);

        $tokens = $this->driver->retrieve($channel);

        $this->assertSame([$token], $tokens);
    }

    /**
     * @depends testExpoInstantiates
     */
    public function testTheMysqlDriverCanForgetAToken(Expo $expo)
    {
        $channel = 'default';
        $token1 = 'ExponentPushToken[some-obscure-token-1]';
        $token2 = 'ExponentPushToken[some-obscure-token-2]';
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

    protected function tearDown(): void
    {
        // Delete all existing channels
        $this->conn->getQuery()
            ->delete($this->table)
            ->executeQuery();

        $this->conn = null;
    }
}
