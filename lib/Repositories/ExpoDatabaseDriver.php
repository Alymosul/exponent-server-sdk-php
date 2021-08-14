<?php

namespace ExponentPhpSDK\Repositories;

use Doctrine\DBAL\DriverManager;
use ExponentPhpSDK\Env;
use ExponentPhpSDK\ExpoRepository;

class ExpoDatabaseDriver implements ExpoRepository
{
    private $env;
    private $db;

    public function __construct()
    {
        $this->env = new Env();
        $this->db = $this->getConnction();
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    private function getConnction()
    {
        $credentials = [
            'dbname' => $this->env->get('DB_DATABASE'),
            'user' => $this->env->get('DB_USERNAME'),
            'password' => $this->env->get('DB_PASSWORD'),
            'host' => $this->env->get('DB_HOST'),
            'port' => $this->env->get('DB_PORT'),
            'driver' => 'pdo_mysql',
        ];

        return DriverManager::getConnection($credentials);
    }

    private function getQuery()
    {
        return $this->db->createQueryBuilder();
    }

    private function channelExists(string $channel)
    {
        return (bool) $this->getQuery()
            ->select('channel')
            ->from('expo') // @todo custom table name
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();
    }

    private function createChannel($channel)
    {
        $this->getQuery()
            ->insert('expo') // @todo custom table
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
        $this->getQuery()
            ->delete('expo') // @todo custom table\
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->executeStatement();

        return true;
    }

    private function getRecipients(string $channel): array
    {
        if (! $this->channelExists($channel)) {
            throw new \Exception(
                sprintf("Interest '%s' does not exist.", $channel)
            );
        }

        $result = $this->getQuery()
            ->select('recipients')
            ->from('expo') // @todo custom table name
            ->where('channel = :channel')
            ->setParameter('channel', $channel)
            ->fetchOne();

        return $result ? json_decode($result) : [];
    }

    private function updateSubscriptions(string $channel, array $recipients): bool
    {
        if (! $this->channelExists($channel)) {
            throw new \Exception(
                sprintf("Interest '%s' does not exist.", $channel)
            );
        }

        $this->getQuery()
            ->update('expo') // @todo custom table
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
