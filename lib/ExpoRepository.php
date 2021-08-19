<?php
namespace ExponentPhpSDK;

interface ExpoRepository
{
    /**
     * Stores an Expo token with a given identifier
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function store(string $key, string $value): bool;

    /**
     * Retrieve an Expo token with a given identifier
     *
     * @param string $key
     *
     * @return array|string|null
     */
    public function retrieve(string $key);

    /**
     * Removes an Expo token with a given identifier
     *
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function forget(string $key, string $value = null): bool;

    /**
     * Removes all Expo tokens with a given identifier
     *
     * @param string $key
     *
     * @return bool
     */
    public function forgetAll(string $key): bool;
}
