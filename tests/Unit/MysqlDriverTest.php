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

    public function testMysqlDriverKeyReturnsAMysqlDriver()
    {
        $expo = Expo::driver('mysql');

        $this->assertEquals('mysql', $expo->getDriver());
    }

    public function testTheMysqlDriverCanStoreTokens()
    {
        $channel = 'default';
        $this->driver->store($channel, 'ExponentPushToken[zzzzzzzzzzzzzzzz]');

        $result = (bool) $this->conn->getQuery()
            ->select('channel')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();

        $this->assertTrue($result);
    }

    public function testYouCanSubscribeToAChannel()
    {
        $channel = 'events';
        $token = 'ExponentPushToken[zzzzzzzzzzzzzzzz]';
        $expo = Expo::driver('mysql');
        $expo->subscribe($channel, $token);

        $result = $this->conn->getQuery()
            ->select('recipients')
            ->from($this->table)
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();

        $this->assertSame([$token], json_decode($result));
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
