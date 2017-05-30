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
    public function store($key, $value): bool;

    /**
     * Retrieve an Expo token with a given identifier
     *
     * @param string $key
     *
     * @return string|null
     */
    public function retrieve(string $key);

    /**
     * Removes an Expo token with a given identifier
     *
     * @param string $key
     *
     * @return bool
     */
    public function forget(string $key): bool;
}
