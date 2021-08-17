<?php

namespace ExponentPhpSDK;

class Env {

    /**
     * Retrieves an environment variable.
     *
     * @return string
     */
    public function get(string $key)
    {
        $value = $_SERVER[$key] ?? null;

        // Allows for a custom table name for databse drivers.
        if ($key === 'EXPO_TABLE') {
            return $value ?? 'expo_tokens';
        }

        return $value;
    }

    /**
     * Determine if an environment variable exists.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key)
    {
        return (bool) $this->get($key);
    }
}
