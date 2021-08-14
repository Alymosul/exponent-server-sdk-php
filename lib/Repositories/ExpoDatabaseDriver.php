<?php

namespace ExponentPhpSDK\Repositories;

use ExponentPhpSDK\Database\Connection;
use ExponentPhpSDK\Env;
use ExponentPhpSDK\Exceptions\ExpoException;
use ExponentPhpSDK\ExpoRepository;

class ExpoDatabaseDriver implements ExpoRepository
{
    private $env;
    private $conn;

    public function __construct(Connection $connection)
    {
        $this->env = new Env();
        $this->conn = $connection->connect();
    }

    private function channelExists(string $channel)
    {
        return (bool) $this->conn->getQuery()
            ->select('channel')
            ->from($this->env->get('EXPO_TABLE'))
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();
    }

    private function createChannel($channel)
    {
        $this->conn->getQuery()
            ->insert($this->env->get('EXPO_TABLE'))
            ->values([
                'channel' => ':channel',
                'recipients' => ':recipients',
            ])
            ->setParameter('channel', $channel)
            ->setParameter('recipients', '[]')
            ->executeStatement();
    }

    private function deleteChannel(string $channel): bool
    {
        $this->conn->getQuery()
            ->delete($this->env->get('EXPO_TABLE'))
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->executeStatement();

        return true;
    }

    private function getRecipients(string $channel): array
    {
        if (! $this->channelExists($channel)) {
            throw new ExpoException(
                sprintf("Interest '%s' does not exist.", $channel)
            );
        }

        $result = $this->conn->getQuery()
            ->select('recipients')
            ->from($this->env->get('EXPO_TABLE'))
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();

        return $result ? json_decode($result) : [];
    }

    private function updateSubscriptions(string $channel, array $recipients): bool
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
            ->setParameter('recipients', json_encode($recipients))
            ->setParameter('channel', $channel)
            ->executeStatement();

        return true;
    }

    /**
     * @param string $channel
     * @param string $token
     */
    public function store($channel, $token): bool
    {
        if (! $this->channelExists($channel)) {
            $this->createChannel($channel);
        }

        $recipients = $this->getRecipients($channel);

        // prevents duplicate subscriptions to the same channel
        if (! in_array($token, $recipients)) {
            array_push($recipients, $token);
        }

        return $this->updateSubscriptions(
            $channel,
            $recipients
        );
    }

    /**
     * @return array|null
     */
    public function retrieve(string $channel)
    {
        $recipients = $this->getRecipients($channel);

        return count($recipients)
            ? $recipients
            : null;
    }

    public function forget(string $channel, string $token = null): bool
    {
        if (! $this->channelExists($channel)) {
            return true;
        }

        if (! $token && count($this->getRecipients($channel)) === 0) {
            return $this->deleteChannel($channel);
        }

        $recipients = $this->getRecipients($channel);

        if (! in_array($token, $recipients)) {
            return false;
        }

        // @todo Can use array_search and unset once we prevent duplicate subscriptions to the same channel.
        $filteredRecipients = array_filter($recipients, function($item) use ($token) {
            return $item !== $token;
        });

        // If there are no more subscribers delete the channel, otherwise update.
        return count($filteredRecipients)
            ? $this->updateSubscriptions($channel, array_values($filteredRecipients))
            : $this->deleteChannel($channel);
    }
}
