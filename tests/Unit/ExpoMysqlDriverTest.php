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
            ->execute();

        $this->conn->close();
        $this->conn = null;
    }

    /** @test */
    public function expo_instantiates()
    {
        $expo = Expo::driver('mysql');

        $this->assertInstanceOf(Expo::class, $expo);

        return $expo;
    }

    /**
     * @depends expo_instantiates
     * @test
     */
    public function expo_returns_mysql_driver(Expo $expo)
    {
        $this->assertEquals('mysql', $expo->getDriver());
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

        $result = $this->conn->getQuery()
            ->select('recipients')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->execute()
            ->fetchOne();

        $this->assertSame([$token], json_decode($result));
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

        $subscriptions = $this->conn->getQuery()
            ->select('recipients')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->execute()
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
            ->execute()
            ->fetchOne();

        // one token subscribed
        $this->assertSame(
            [$token2],
            json_decode($subscriptions)
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

        $subscriptions = $this->conn->getQuery()
            ->select('recipients')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->execute()
            ->fetchOne();

        // two tokens subscribed
        $this->assertSame(
            [$token1, $token2],
            json_decode($subscriptions)
        );

        $expo->unsubscribeAll($channel);

        $channel = $this->conn->getQuery()
            ->select('channel')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->execute()
            ->fetchOne();

        // channel is deleted
        $this->assertSame(false, $channel);
    }

    /**
     * @depends expo_instantiates
     * @test
     */
    public function mysql_driver_can_forget_a_token(Expo $expo)
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
            ->execute()
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
            ->execute()
            ->fetchOne();

        // one token subscribed
        $this->assertSame(
            [$token2],
            json_decode($subscriptions)
        );
    }

    /** @test */
    public function mysql_driver_can_store_tokens()
    {
        $channel = 'default';
        $this->driver->store($channel, 'ExponentPushToken[token]');

        $result = (bool) $this->conn->getQuery()
            ->select('channel')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->execute()
            ->fetchOne();

        $this->assertTrue($result);
    }

    /** @test */
    public function mysql_driver_can_retrieve_tokens()
    {
        $channel = 'default';
        $token = 'ExponentPushToken[token]';
        $this->driver->store($channel, $token);

        $tokens = $this->driver->retrieve($channel);

        $this->assertSame([$token], $tokens);
    }
}
