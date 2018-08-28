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
        if(isset($storageInstance->{$key}))
        {
            // If there is a single token, make it an array so we can push the additional tokens in it
            if(!is_array($storageInstance->{$key}))
            {
                $storageInstance->{$key} = [$storageInstance->{$key}];
            }

            // Add new token to existing key
            array_push($storageInstance->{$key}, $value);
        }
        else
        {
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
     *
     * @return bool
     */
    public function forget(string $key): bool
    {
        $storageInstance = null;
        try {
            $storageInstance = $this->getRepository();
        } catch (\Exception $e) {
            return false;
        }

        unset($storageInstance->{$key});

        $this->updateRepository($storageInstance);

        return !isset($storageInstance->{$key});
    }

    /**
     * Gets the storage file contents and converts it into an object
     *
     * @return mixed
     */
    private function getRepository()
    {
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
     * @return bool|string
     */
    private function createFile()
    {
        $file = fopen($this->storage, "w");
        fputs($file, '{}');
        fclose($file);
        return json_decode('{}');
    }
}
