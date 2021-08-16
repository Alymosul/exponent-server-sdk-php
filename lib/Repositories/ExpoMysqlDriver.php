<?php

namespace ExponentPhpSDK\Repositories;

use ExponentPhpSDK\Database\Connection;
use ExponentPhpSDK\Env;
use ExponentPhpSDK\Exceptions\ExpoException;
use ExponentPhpSDK\ExpoRepository;

class ExpoMysqlDriver implements ExpoRepository
{
    /**
     * Access to environment variables.
     *
     * @var Env
     */
    private $env;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $conn;

    /**
     * ExpoMysqlDriver constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->env = new Env();
        $this->conn = $connection->connect();
    }

    /**
     * Subscribes a token to the given channel.
     *
     * @param string $channel
     * @param string $token
     * @return bool
     */
    public function store(string $channel, string $token): bool
    {
        if (! $this->channelExists($channel)) {
            $this->createChannel($channel);
        }

        $tokens = $this->getTokens($channel);

        // prevents duplicate subscriptions to the same channel
        if (! in_array($token, $tokens)) {
            array_push($tokens, $token);
        }

        return $this->updateSubscriptions(
            $channel,
            $tokens
        );
    }

    /**
     * Retrieves a channels tokens.
     *
     * @param string $channel
     * @return array|null
     */
    public function retrieve(string $channel)
    {
        $tokens = $this->getTokens($channel);

        return count($tokens)
            ? $tokens
            : null;
    }

    /**
     * Removes a token from a channel.
     *
     * @param string $channel
     * @param string $token
     * @return bool
     */
    public function forget(string $channel, string $token = null): bool
    {
        if (! $this->channelExists($channel)) {
            return true;
        }

        $tokens = $this->getTokens($channel);

        if (! $token && count($tokens) === 0) {
            return $this->deleteChannel($channel);
        }

        if (! in_array($token, $tokens)) {
            return false;
        }

        $filteredTokens = array_filter($tokens, function($item) use ($token) {
            return $item !== $token;
        });

        // If there are no more subscribers delete the channel, otherwise update.
        return count($filteredTokens)
            ? $this->updateSubscriptions($channel, array_values($filteredTokens))
            : $this->deleteChannel($channel);
    }

    /**
     * Checks if a given channel exists.
     *
     * @param string $channel
     * @return bool
     */
    private function channelExists(string $channel): bool
    {
        return (bool) $this->conn->getQuery()
            ->select('channel')
            ->from($this->env->get('EXPO_TABLE'))
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->execute()
            ->fetchOne();
    }

    /**
     * Creates a channel.
     *
     * @param string $channel
     * @return void
     */
    private function createChannel(string $channel): void
    {
        $this->conn->getQuery()
            ->insert($this->env->get('EXPO_TABLE'))
            ->values([
                'channel' => ':channel',
                'recipients' => ':recipients',
            ])
            ->setParameter('channel', $channel)
            ->setParameter('recipients', '[]')
            ->execute();
    }

    /**
     * Deletes a channel.
     *
     * @param string $channel
     * @return bool
     */
    private function deleteChannel(string $channel): bool
    {
        $this->conn->getQuery()
            ->delete($this->env->get('EXPO_TABLE'))
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->execute();

        return true;
    }

    /**
     * Gets tokens for a given channel.
     *
     * @param string $channel
     * @return array
     */
    private function getTokens(string $channel): array
    {
        if (! $this->channelExists($channel)) {
            throw new ExpoException(
                sprintf("Interest '%s' does not exist.", $channel)
            );
        }

        $tokens = $this->conn->getQuery()
            ->select('recipients')
            ->from($this->env->get('EXPO_TABLE'))
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->execute()
            ->fetchOne();

        return $tokens ? json_decode($tokens) : [];
    }

    /**
     * Updates a channels tokens.
     *
     * @param string $channel
     * @param array $tokens
     * @return bool
     */
    private function updateSubscriptions(string $channel, array $tokens): bool
    {
        if (! $this->channelExists($channel)) {
            throw new ExpoException(
                sprintf("Interest '%s' does not exist.", $channel)
            );
        }

        $this->conn->getQuery()
            ->update($this->env->get('EXPO_TABLE'))
            ->set('recipients', ':recipients')
            ->where('channel = :channel')
            ->setParameter('recipients', json_encode($tokens))
            ->setParameter('channel', $channel)
            ->execute();

        return true;
    }
}
