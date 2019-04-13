<?php

namespace ExponentPhpSDK\Repositories;

use ExponentPhpSDK\ExpoRepository;

class ExpoFileDriver implements ExpoRepository
{
    /**
     * The file path for the file that will contain the registered tokens
     *
     * @var string
     */
    private $storage = __DIR__.'/../../storage/tokens.json';

    /**
     * Stores an Expo token with a given identifier
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function store($key, $value): bool
    {
        $storageInstance = null;

        try {
            $storageInstance = $this->getRepository();
        } catch (\Exception $e) {
            // Create the file, if it does not exist..
            $storageInstance = $this->createFile();
        }

        // Check for existing tokens
        if (isset($storageInstance->{$key})) {
            // If there is a single token, make it an array so we can push the additional tokens in it
            if (!is_array($storageInstance->{$key})) {
                $storageInstance->{$key} = [$storageInstance->{$key}];
            }

            // Prevent duplicates
            if (!in_array($value, $storageInstance->{$key})) {
                // Add new token to existing key
                array_push($storageInstance->{$key}, $value);
            }
        } else {
            // First token for this key
            $storageInstance->{$key} = [$value];
        }

        $file = $this->updateRepository($storageInstance);

        return (bool) $file;
    }

    /**
     * Retrieves an Expo token with a given identifier
     *
     * @param string $key
     *
     * @return array|string|null
     */
    public function retrieve(string $key)
    {
        $token = null;

        $storageInstance = $this->getRepository();

        $token = $storageInstance->{$key}?? null;

        return $token;
    }

    /**
     * Removes an Expo token with a given identifier
     *
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function forget(string $key, string $value = null): bool
    {
        $storageInstance = null;

        try {
            $storageInstance = $this->getRepository();
        } catch (\Exception $e) {
            return false;
        }

        // Delete a single token with this key and check if there are multiple tokens associated with this key
        if($value && isset($storageInstance->{$key}) && is_array($storageInstance->{$key}) && count($storageInstance->{$key}) > 1)
        {
            // Find our token in list of tokens
            $index = array_search($value, $storageInstance->{$key});

            if (isset($index) && isset($storageInstance->{$key}[$index])) {
                // Remove single token from list
                unset($storageInstance->{$key}[$index]);

                if (count($storageInstance->{$key}) === 0) {
                    // No more tokens left, remove key
                    unset($storageInstance->{$key});
                } else {
                    // Reset array key after removing an key
                    $storageInstance->{$key} = array_values($storageInstance->{$key});
                }

                $this->updateRepository($storageInstance);

                return !in_array($value, $storageInstance->{$key});
            }
        } else {
            // Delete all tokens with this key
            unset($storageInstance->{$key});

            $this->updateRepository($storageInstance);

            return !isset($storageInstance->{$key});
        }

        return false;
    }

    /**
     * Gets the storage file contents and converts it into an object
     *
     * @return object
     *
     * @throws \Exception
     */
    private function getRepository()
    {
        if (!file_exists($this->storage)) {
            throw new \Exception('Tokens storage file not found.');
        }

        $file = file_get_contents($this->storage);
        return json_decode($file);
    }

    /**
     * Updates the storage file with the new contents
     *
     * @param $contents
     *
     * @return bool|int
     */
    private function updateRepository($contents)
    {
        $record = json_encode($contents);
        return file_put_contents($this->storage, $record);
    }

    /**
     * Creates the storage file
     *
     * @return object
     */
    private function createFile()
    {
        $file = fopen($this->storage, "w");
        fputs($file, '{}');
        fclose($file);
        return json_decode('{}');
    }

    /**
     * Allows for custom token storage path
     *
     * @param  string $storage path to token storage json file
     * @return self
     */
    public function setStorage(string $storage): self
    {
        $this->storage = $storage;
        return $this;
    }
}
